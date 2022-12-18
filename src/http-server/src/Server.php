<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Contracts\Http\RequestInterface as ServerRequestInterface;
use Larmias\Contracts\Http\ResponseInterface as ServerResponseInterface;
use Larmias\HttpServer\Contracts\ExceptionHandlerInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\HttpServer\Routing\Router;
use Larmias\Routing\Dispatched;
use Larmias\HttpServer\Routing\Middleware as RouteMiddleware;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Server implements OnRequestInterface
{
    /** @var string */
    public const ON_REQUEST = 'onRequest';

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(protected ContainerInterface $container,protected  EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ServerResponseInterface $serverResponse
     */
    public function onRequest(ServerRequestInterface $serverRequest, ServerResponseInterface $serverResponse): void
    {
        $this->container->instance(ServerRequestInterface::class, $serverRequest);
        $this->container->instance(ServerResponseInterface::class, $serverResponse);
        $request = $this->makeRequest($serverRequest, $serverResponse);
        try {
            $response = $this->runWithRequest($request);
        } catch (Throwable $e) {
            $response = $this->getExceptionResponse($request, $e);
        } finally {
            $response->send();
        }
    }

    /**
     * @param RequestInterface $request
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected function getExceptionResponse(RequestInterface $request, Throwable $e): ResponseInterface
    {
        /** @var ExceptionHandlerInterface $handler */
        $handler = $this->container->make(ExceptionHandler::class);
        $handler->report($e);
        return $handler->render($request, $e);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function runWithRequest(RequestInterface $request): ResponseInterface
    {
        /** @var Middleware $middleware */
        $middleware = $this->container->make(Middleware::class);
        return $middleware->pipeline()->send($request)->then(function (RequestInterface $request) {
            return $this->dispatchRoute($request);
        });
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function dispatchRoute(RequestInterface $request): ResponseInterface
    {
        $dispatched = Router::getRouteCollector()->dispatch($request->getMethod(), $request->getPathInfo());
        $this->container->instance(RequestInterface::class, $request = $request->withAttribute(Dispatched::class, $dispatched));
        $option = $dispatched->rule->getOption();
        /** @var RouteMiddleware $middleware */
        $middleware = $this->container->make(RouteMiddleware::class);
        return $middleware->import($option['middleware'])->pipeline()->send($request)->then(function (RequestInterface $request) use ($dispatched) {
            return $dispatched->dispatcher->run($request->all());
        });
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ServerResponseInterface $serverResponse
     * @return RequestInterface
     */
    protected function makeRequest(ServerRequestInterface $serverRequest, ServerResponseInterface $serverResponse): RequestInterface
    {
        $request = Request::loadFormRequest($serverRequest);
        $this->container->instance(RequestInterface::class, $request);
        $this->container->instance(ResponseInterface::class, new Response($serverResponse));
        return $request;
    }
}