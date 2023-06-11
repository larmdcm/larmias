<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Exceptions\Handler;

use Larmias\Contracts\SessionInterface;
use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;
use Larmias\ExceptionHandler\Render\HtmlRender;
use Larmias\ExceptionHandler\Render\JsonRender;
use Larmias\HttpServer\Contracts\ExceptionHandlerInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\HttpServer\Exceptions\HttpException;
use Larmias\HttpServer\Exceptions\HttpResponseException;
use Larmias\Routing\Exceptions\RouteMethodNotAllowedException;
use Larmias\Routing\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\ExceptionHandler\Contracts\RenderInterface;
use Throwable;
use Closure;
use function str_contains;

class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @param RequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     */
    public function render(RequestInterface $request, Throwable $e): PsrResponseInterface
    {
        return $this->whenResponse($e, function ($e) use ($request) {
            return $this->getRenderResponse($request, $e)->withStatus($this->getHttpCode($e));
        });
    }

    /**
     * @param Throwable $e
     * @param Closure $callback
     * @return PsrResponseInterface
     */
    public function whenResponse(Throwable $e, Closure $callback): PsrResponseInterface
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }
        return $callback($e);
    }

    /**
     * @param Throwable $e
     * @param mixed $result
     * @param mixed|null $args
     * @return PsrResponseInterface
     */
    public function handle(Throwable $e, mixed $result, mixed $args = null): PsrResponseInterface
    {
        return $this->render($args, $e);
    }

    /**
     * @param Throwable $e
     * @return int
     */
    protected function getHttpCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        $code = 500;
        if ($e instanceof RouteNotFoundException) {
            $code = 404;
        } else if ($e instanceof RouteMethodNotAllowedException) {
            $code = 403;
        }
        return $code;
    }

    /**
     * @param RequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     */
    protected function getRenderResponse(RequestInterface $request, Throwable $e): PsrResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->container->make(ResponseInterface::class);
        $render = $this->getRender($request);
        $headers = $e instanceof HttpException ? $e->getHeaders() : [];
        if ($this->isJsonRequest($request)) {
            $response = $response->json($render->render($e), headers: $headers);
        } else {
            $response = $response->html($render->render($e), headers: $headers);
        }
        return $response;
    }

    /**
     * @param RequestInterface $request
     * @return RenderInterface
     */
    protected function getRender(RequestInterface $request): RenderInterface
    {
        /** @var RenderInterface $render */
        $render = $this->container->make($this->isJsonRequest($request) ? JsonRender::class : HtmlRender::class, [], true);
        $render->addDataTableCallback('PSR7 Query', [$request, 'getQueryParams']);
        $render->addDataTableCallback('PSR7 Post', [$request, 'getParsedBody']);
        $render->addDataTableCallback('PSR7 Server', [$request, 'getServerParams']);
        $render->addDataTableCallback('PSR7 Cookie', [$request, 'getCookieParams']);
        $render->addDataTableCallback('PSR7 File', [$request, 'getUploadedFiles']);
        $render->addDataTableCallback('PSR7 Attribute', [$request, 'getAttributes']);
        if ($this->container->has(SessionInterface::class)) {
            $render->addDataTableCallback('Larmias Session', [$this->container->get(SessionInterface::class), 'all']);
        }
        return $render;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    protected function isJsonRequest(RequestInterface $request): bool
    {
        return str_contains($request->getHeaderLine('accept'), 'json');
    }
}