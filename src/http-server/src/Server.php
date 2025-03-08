<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use FastRoute\Dispatcher;
use Larmias\Contracts\Arrayable;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Dispatcher\DispatcherFactoryInterface;
use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Contracts\Http\RequestInterface;
use Larmias\Contracts\Http\ResponseInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;
use Larmias\Contracts\Jsonable;
use Larmias\ExceptionHandler\Contracts\ExceptionHandlerDispatcherInterface;
use Larmias\Http\Message\ServerRequest;
use Larmias\Http\Message\Stream;
use Larmias\HttpServer\Events\HttpRequestStart;
use Larmias\HttpServer\Events\HttpRequestEnd;
use Larmias\HttpServer\Exceptions\Handler\ExceptionHandler;
use Larmias\HttpServer\CoreMiddleware\HttpCoreMiddleware;
use Larmias\HttpServer\CoreMiddleware\HttpRouteCoreMiddleware;
use Larmias\Http\Message\ServerResponse as PsrResponse;
use Larmias\Contracts\ContainerInterface;
use Larmias\HttpServer\Routing\Router;
use Larmias\Routing\Dispatched;
use Larmias\Collection\Arr;
use Larmias\Codec\Json;
use Larmias\Routing\Exceptions\RouteMethodNotAllowedException;
use Larmias\Routing\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function Larmias\Support\println;
use function Larmias\Support\format_exception;

class Server implements OnRequestInterface
{
    /**
     * 初始化
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $eventDispatcher
     * @param ResponseEmitter $responseEmitter
     * @param HttpCoreMiddleware $httpCoreMiddleware
     * @param ContextInterface $context
     * @param ConfigInterface $config
     * @param DispatcherFactoryInterface $dispatcherFactory
     */
    public function __construct(
        protected ContainerInterface         $container,
        protected EventDispatcherInterface   $eventDispatcher,
        protected ResponseEmitterInterface   $responseEmitter,
        protected HttpCoreMiddleware         $httpCoreMiddleware,
        protected ContextInterface           $context,
        protected ConfigInterface            $config,
        protected DispatcherFactoryInterface $dispatcherFactory,
    )
    {
    }

    /**
     * 请求回调事件
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws Throwable
     */
    public function onRequest(RequestInterface $request, ResponseInterface $response): void
    {
        $serverReq = $this->makeRequest($request, $response);
        $this->eventDispatcher->dispatch(new HttpRequestStart($serverReq));
        try {
            $psrResponse = $this->runWithRequest($serverReq);
        } catch (Throwable $e) {
            try {
                $psrResponse = $this->getExceptionResponse($serverReq, $e);
            } catch (Throwable $exception) {
                println(format_exception($exception));
            }
        } finally {
            try {
                if (isset($psrResponse)) {
                    $this->eventDispatcher->dispatch(new HttpRequestEnd($serverReq, $psrResponse));
                    $this->responseEmitter->emit($psrResponse, $response, $serverReq->getMethod() !== 'HEAD');
                }
            } catch (Throwable $e) {
                println(format_exception($e));
            }
        }
    }

    /**
     * 运行请求方法
     * @param ServerRequestInterface $request
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function runWithRequest(ServerRequestInterface $request): PsrResponseInterface
    {
        $dispatched = Router::dispatch($request->getMethod(), $request->getUri()->getPath());
        $this->context->set(ServerRequestInterface::class, $request = $request->withAttribute(Dispatched::class, $dispatched));
        return $this->httpCoreMiddleware->dispatch($request, function (ServerRequestInterface $request) {
            return $this->dispatchRouter($request);
        });
    }

    /**
     * 路由调度
     * @param ServerRequestInterface $request
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function dispatchRouter(ServerRequestInterface $request): PsrResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
        if ($dispatched->status !== Dispatcher::FOUND) {
            throw new ($dispatched->status === Dispatcher::NOT_FOUND ? RouteNotFoundException::class : RouteMethodNotAllowedException::class);
        }
        $option = $dispatched->rule->getOption();
        /** @var HttpRouteCoreMiddleware $httpRouteCoreMiddleware */
        $httpRouteCoreMiddleware = $this->container->get(HttpRouteCoreMiddleware::class);
        return $httpRouteCoreMiddleware->set($option['middleware'])->dispatch($request, function () use ($dispatched) {
            $dispatcher = $this->dispatcherFactory->make($dispatched->rule);
            return $this->wrapResultAsResponse($dispatcher->dispatch($dispatched->params));
        });
    }

    /**
     * 将结果包装成响应
     * @param mixed $result
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function wrapResultAsResponse(mixed $result): PsrResponseInterface
    {
        if ($result instanceof PsrResponseInterface) {
            return $result;
        }

        /** @var PsrResponseInterface $response */
        $response = $this->context->get(PsrResponseInterface::class);

        if (is_array($result) || $result instanceof Arrayable) {
            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(Stream::create(Json::encode($result)));
        }

        if (is_string($result)) {
            return $response->withAddedHeader('content-type', 'text/plain')->withBody(Stream::create($result));
        }

        if ($result instanceof Jsonable) {
            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(Stream::create($result->toJson()));
        }

        $result = (string)$result;

        if ($response->hasHeader('content-type')) {
            return $response->withBody(Stream::create($result));
        }

        return $response->withHeader('content-type', 'text/plain')->withBody(Stream::create($result));
    }

    /**
     * 获取异常响应对象
     * @param ServerRequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function getExceptionResponse(ServerRequestInterface $request, Throwable $e): PsrResponseInterface
    {
        $handlers = Arr::wrap($this->config->get('exceptions.handler.http', []));
        $check = !empty(array_filter($handlers, fn($handler) => is_subclass_of($handler, ExceptionHandler::class)));
        if (!$check) {
            $handlers[] = ExceptionHandler::class;
        }
        /** @var ExceptionHandlerDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get(ExceptionHandlerDispatcherInterface::class);
        return $dispatcher->dispatch($e, $handlers, $request);
    }

    /**
     * 创建server request
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ServerRequestInterface
     * @throws Throwable
     */
    protected function makeRequest(RequestInterface $request, ResponseInterface $response): ServerRequestInterface
    {
        $psrResponse = new PsrResponse();
        $psrResponse->setRawResponse($response);
        $serverReq = ServerRequest::loadFromRequest($request);
        $this->context->set(ServerRequestInterface::class, $serverReq);
        $this->context->set(PsrResponseInterface::class, $psrResponse);
        return $serverReq;
    }
}
