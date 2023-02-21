<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Contracts\Http\RequestInterface as HttpRequestInterface;
use Larmias\Contracts\Http\ResponseInterface as HttpResponseInterface;
use Larmias\Http\Message\ServerRequest;
use Larmias\HttpServer\Events\HttpRequestStart;
use Larmias\HttpServer\Events\HttpRequestEnd;
use Larmias\HttpServer\Exceptions\Handler\ExceptionHandler;
use Larmias\HttpServer\Message\Request;
use Larmias\HttpServer\Message\Response;
use Larmias\HttpServer\Middleware\HttpMiddleware;
use Larmias\HttpServer\Middleware\HttpRouteMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Http\Message\Response as PsrResponse;
use Larmias\HttpServer\Contracts\ExceptionHandlerInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\HttpServer\Routing\Router;
use Larmias\Routing\Dispatched;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function Larmias\Utils\println;
use function Larmias\Utils\format_exception;

class Server implements OnRequestInterface
{
    /**
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $eventDispatcher
     * @param ResponseEmitter $responseEmitter
     */
    public function __construct(
        protected ContainerInterface       $container,
        protected EventDispatcherInterface $eventDispatcher,
        protected ResponseEmitter          $responseEmitter)
    {
    }

    /**
     * @param HttpRequestInterface $serverRequest
     * @param HttpResponseInterface $serverResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onRequest(HttpRequestInterface $serverRequest, HttpResponseInterface $serverResponse): void
    {
        $request = $this->makeRequest($serverRequest, $serverResponse);
        $this->eventDispatcher->dispatch(new HttpRequestStart($request));
        try {
            $response = $this->runWithRequest($request);
        } catch (Throwable $e) {
            try {
                $response = $this->getExceptionResponse($request, $e);
            } catch (Throwable $exception) {
                println(format_exception($exception));
            }
        } finally {
            if (isset($response)) {
                $this->eventDispatcher->dispatch(new HttpRequestEnd($request, $response));
                $this->responseEmitter->emit($response, $serverResponse);
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     */
    protected function getExceptionResponse(RequestInterface $request, Throwable $e): PsrResponseInterface
    {
        /** @var ExceptionHandlerInterface $handler */
        $handler = $this->container->make(ExceptionHandler::class);
        $handler->report($e);
        return $handler->render($request, $e);
    }

    /**
     * @param RequestInterface $request
     * @return PsrResponseInterface
     */
    protected function runWithRequest(RequestInterface $request): PsrResponseInterface
    {
        /** @var HttpMiddleware $middleware */
        $middleware = $this->container->make(HttpMiddleware::class);
        return $middleware->pipeline()->send($request)->then(function (RequestInterface $request) {
            return $this->dispatchRoute($request);
        });
    }
    
    /**
     * @param RequestInterface $request
     * @return PsrResponseInterface
     */
    protected function dispatchRoute(RequestInterface $request): PsrResponseInterface
    {
        $dispatched = Router::dispatch($request->getMethod(), $request->getPathInfo());
        $this->container->instance(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, $dispatched));
        $option = $dispatched->rule->getOption();
        /** @var HttpRouteMiddleware $middleware */
        $middleware = $this->container->make(HttpRouteMiddleware::class, [], true);
        return $middleware->import($option['middleware'])->pipeline()->send($request)->then(function (RequestInterface $request) use ($dispatched) {
            return $this->warpResultToResponse($dispatched->dispatcher->run($request->all()));
        });
    }

    /**
     * @param mixed $result
     * @return PsrResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function warpResultToResponse(mixed $result): PsrResponseInterface
    {
        if ($result instanceof PsrResponseInterface) {
            return $result;
        }
        /** @var ResponseInterface $response */
        $response = $this->container->get(ResponseInterface::class);
        return \is_scalar($result) || $result instanceof \Stringable ? $response->html((string)$result) : $response->json($result);
    }

    /**
     * @param HttpRequestInterface $httpRequest
     * @param HttpResponseInterface $httpResponse
     * @return RequestInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function makeRequest(HttpRequestInterface $httpRequest, HttpResponseInterface $httpResponse): RequestInterface
    {
        $this->container->instance(HttpRequestInterface::class, $httpRequest);
        $this->container->instance(HttpResponseInterface::class, $httpResponse);
        $this->container->instance(ServerRequestInterface::class, ServerRequest::loadFromRequest($httpRequest));
        $this->container->instance(PsrResponseInterface::class, new PsrResponse());
        $this->container->instance(RequestInterface::class, new Request($this->container));
        $this->container->instance(ResponseInterface::class, new Response($this->container));
        return $this->container->get(RequestInterface::class);
    }
}