<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Contracts\Http\RequestInterface as RawRequestInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;
use Larmias\Contracts\Http\ResponseInterface as RawResponseInterface;
use Larmias\ExceptionHandler\Contracts\ExceptionHandlerDispatcherInterface;
use Larmias\Http\Message\ServerRequest;
use Larmias\HttpServer\Events\HttpRequestStart;
use Larmias\HttpServer\Events\HttpRequestEnd;
use Larmias\HttpServer\Exceptions\Handler\ExceptionHandler;
use Larmias\HttpServer\Message\Request;
use Larmias\HttpServer\Message\Response;
use Larmias\HttpServer\CoreMiddleware\HttpCoreMiddleware;
use Larmias\HttpServer\CoreMiddleware\HttpRouteCoreMiddleware;
use Larmias\Http\Message\ServerResponse as PsrResponse;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\HttpServer\Routing\Router;
use Larmias\Routing\Dispatched;
use Larmias\Utils\Arr;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Stringable;
use function Larmias\Utils\println;
use function Larmias\Utils\format_exception;
use function is_scalar;

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
     */
    public function __construct(
        protected ContainerInterface       $container,
        protected EventDispatcherInterface $eventDispatcher,
        protected ResponseEmitterInterface $responseEmitter,
        protected HttpCoreMiddleware       $httpCoreMiddleware,
        protected ContextInterface         $context,
        protected ConfigInterface          $config
    )
    {
    }

    /**
     * 请求回调事件
     * @param RawRequestInterface $request
     * @param RawResponseInterface $response
     */
    public function onRequest(RawRequestInterface $request, RawResponseInterface $response): void
    {
        $request = $this->makeRequest($request, $response);
        $this->eventDispatcher->dispatch(new HttpRequestStart($request));
        try {
            $psrResponse = $this->runWithRequest($request);
        } catch (Throwable $e) {
            try {
                $psrResponse = $this->getExceptionResponse($request, $e);
            } catch (Throwable $exception) {
                println(format_exception($exception));
            }
        } finally {
            if (isset($psrResponse)) {
                $this->eventDispatcher->dispatch(new HttpRequestEnd($request, $psrResponse));
                $this->responseEmitter->emit($psrResponse, $response, $request->getMethod() !== 'HEAD');
            }
        }
    }

    /**
     * 运行请求方法
     * @param RequestInterface $request
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function runWithRequest(RequestInterface $request): PsrResponseInterface
    {
        return $this->httpCoreMiddleware->dispatch($request, function (RequestInterface $request) {
            return $this->dispatchRouter($request);
        });
    }

    /**
     * 路由调度
     * @param RequestInterface $request
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function dispatchRouter(RequestInterface $request): PsrResponseInterface
    {
        $dispatched = Router::dispatch($request->getMethod(), $request->getPathInfo());
        $this->context->set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, $dispatched));
        $option = $dispatched->rule->getOption();
        /** @var HttpRouteCoreMiddleware $httpRouteCoreMiddleware */
        $httpRouteCoreMiddleware = $this->container->get(HttpRouteCoreMiddleware::class);
        return $httpRouteCoreMiddleware->set($option['middleware'])->dispatch($request, function (RequestInterface $request) use ($dispatched) {
            return $this->warpResultToResponse($dispatched->dispatcher->run($request->all()));
        });
    }

    /**
     * 包装返回结果响应
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
        return is_scalar($result) || $result instanceof Stringable ? $response->html((string)$result) : $response->json($result);
    }

    /**
     * 获取异常响应对象
     * @param RequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     * @throws Throwable
     */
    protected function getExceptionResponse(RequestInterface $request, Throwable $e): PsrResponseInterface
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
     * 创建请求
     * @param RawRequestInterface $rawRequest
     * @param RawResponseInterface $rawResponse
     * @return RequestInterface
     */
    protected function makeRequest(RawRequestInterface $rawRequest, RawResponseInterface $rawResponse): RequestInterface
    {
        $psrResponse = new PsrResponse();
        $psrResponse->setRawResponse($rawResponse);
        $this->context->set(ServerRequestInterface::class, ServerRequest::loadFromRequest($rawRequest));
        $this->context->set(PsrResponseInterface::class, $psrResponse);
        $this->context->set(RequestInterface::class, new Request($this->context));
        $this->context->set(ResponseInterface::class, new Response($this->context));
        return $this->context->get(RequestInterface::class);
    }
}