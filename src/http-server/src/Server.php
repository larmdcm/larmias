<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Contracts\Http\RequestInterface as HttpRequestInterface;
use Larmias\Contracts\Http\ResponseInterface as HttpResponseInterface;
use Larmias\Http\Message\ServerRequest;
use Larmias\HttpServer\Events\HttpRequestStart;
use Larmias\HttpServer\Events\HttpRequestEnd;
use Larmias\HttpServer\Exceptions\Handler\ExceptionHandler;
use Larmias\HttpServer\Message\Request;
use Larmias\HttpServer\Message\Response;
use Larmias\HttpServer\CoreMiddleware\HttpCoreMiddleware;
use Larmias\HttpServer\CoreMiddleware\HttpRouteCoreMiddleware;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Http\Message\ServerResponse as PsrResponse;
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
     * @param HttpCoreMiddleware $httpCoreMiddleware
     * @param ContextInterface $context
     * @param ConfigInterface $config
     */
    public function __construct(
        protected ContainerInterface       $container,
        protected EventDispatcherInterface $eventDispatcher,
        protected ResponseEmitter          $responseEmitter,
        protected HttpCoreMiddleware       $httpCoreMiddleware,
        protected ContextInterface         $context,
        protected ConfigInterface          $config
    )
    {
    }

    /**
     * @param HttpRequestInterface $request
     * @param HttpResponseInterface $response
     */
    public function onRequest(HttpRequestInterface $request, HttpResponseInterface $response): void
    {
        $psrRequest = $this->makeRequest($request, $response);
        $this->eventDispatcher->dispatch(new HttpRequestStart($psrRequest));
        try {
            $psrResponse = $this->runWithRequest($psrRequest);
        } catch (Throwable $e) {
            try {
                $psrResponse = $this->getExceptionResponse($psrRequest, $e);
            } catch (Throwable $exception) {
                println(format_exception($exception));
            }
        } finally {
            if (isset($psrResponse)) {
                $this->eventDispatcher->dispatch(new HttpRequestEnd($psrRequest, $psrResponse));
                $this->responseEmitter->emit($psrResponse, $response, $psrRequest->getMethod() !== 'HEAD');
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @return PsrResponseInterface
     */
    protected function runWithRequest(RequestInterface $request): PsrResponseInterface
    {
        return $this->httpCoreMiddleware->dispatch($request, function (RequestInterface $request) {
            return $this->dispatchRouter($request);
        });
    }

    /**
     * @param RequestInterface $request
     * @return PsrResponseInterface
     */
    protected function dispatchRouter(RequestInterface $request): PsrResponseInterface
    {
        $dispatched = Router::dispatch($request->getMethod(), $request->getPathInfo());
        $this->context->set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, $dispatched));
        $option = $dispatched->rule->getOption();
        /** @var HttpRouteCoreMiddleware $httpRouteCoreMiddleware */
        $httpRouteCoreMiddleware = $this->container->make(HttpRouteCoreMiddleware::class);
        return $httpRouteCoreMiddleware->set($option['middleware'])->dispatch($request, function (RequestInterface $request) use ($dispatched) {
            return $this->warpResultToResponse($dispatched->dispatcher->run($request->all()));
        });
    }

    /**
     * @param mixed $result
     * @return PsrResponseInterface
     */
    protected function warpResultToResponse(mixed $result): PsrResponseInterface
    {
        if ($result instanceof PsrResponseInterface) {
            return $result;
        }
        /** @var ResponseInterface $response */
        $response = $this->context->get(ResponseInterface::class);
        return \is_scalar($result) || $result instanceof \Stringable ? $response->html((string)$result) : $response->json($result);
    }

    /**
     * @param RequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     */
    protected function getExceptionResponse(RequestInterface $request, Throwable $e): PsrResponseInterface
    {
        /** @var ExceptionHandlerInterface $handler */
        $class = $this->config->get('exceptions.handler.http', ExceptionHandler::class);
        $handler = $this->container->make($class);
        $handler->report($e);
        return $handler->render($request, $e);
    }

    /**
     * @param HttpRequestInterface $httpRequest
     * @param HttpResponseInterface $response
     * @return RequestInterface
     */
    protected function makeRequest(HttpRequestInterface $httpRequest, HttpResponseInterface $response): RequestInterface
    {
        $psrResponse = new PsrResponse();
        $psrResponse->setRawResponse($response);
        $this->context->set(ServerRequestInterface::class, ServerRequest::loadFromRequest($httpRequest));
        $this->context->set(PsrResponseInterface::class, $psrResponse);
        $this->context->set(RequestInterface::class, new Request($this->context));
        $this->context->set(ResponseInterface::class, new Response($this->context));
        return $this->context->get(RequestInterface::class);
    }
}