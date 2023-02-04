<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Middleware;

use Larmias\Contracts\ConfigInterface;
use Larmias\Http\CSRF\Contracts\CsrfManagerInterface;
use Larmias\Http\CSRF\Exceptions\TokenMismatchException;
use Larmias\Http\Message\Cookie;
use Larmias\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected array $except = [];

    /**
     * CsrfMiddleware constructor.
     * @param CsrfManagerInterface $csrfManager
     * @param ConfigInterface $config
     */
    public function __construct(protected CsrfManagerInterface $csrfManager, protected ConfigInterface $config)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->csrfManager->init();

        if ($this->isReading($request) || $this->inExceptArray($request) || $this->tokensMatch($request)) {
            return $this->addCookieResponse($request, $handler->handle($request));
        }
        throw new TokenMismatchException();
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isReading(ServerRequestInterface $request): bool
    {
        return \in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS'], true);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function inExceptArray(ServerRequestInterface $request): bool
    {
        $fullUrl = (string)$request->getUri();
        $path = $request->getUri()->getPath();
        foreach ($this->except as $except) {
            $except = '/' . \ltrim($except, '/');
            if (Str::is($except, $fullUrl) || Str::is($except, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function tokensMatch(ServerRequestInterface $request): bool
    {
        if (\method_exists($request, 'input')) {
            $token = $request->input($this->csrfManager->getTokenName());
        } else {
            $inputData = \array_merge($request->getQueryParams(), (array)$request->getParsedBody());
            $token = $inputData[$this->csrfManager->getTokenName()] ?? '';
        }

        if (!$token) {
            $token = $request->getHeaderLine('X-CSRF-TOKEN');
        }

        $sessionToken = $this->csrfManager->getToken();
        if (!$sessionToken || !$token) {
            return false;
        }

        return \hash_equals($sessionToken, $token);
    }

    /**
     * @return int
     */
    protected function getCookieExpire(): int
    {
        $lifeTime = $this->config->get('session.cookie_lifetime', 0);
        return $lifeTime > 0 ? \time() + $lifeTime : 0;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function addCookieResponse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = $this->csrfManager->getToken();
        if (!$token) {
            return $response;
        }
        $uri = $request->getUri();
        $path = '/';
        $secure = \strtolower($uri->getScheme()) === 'https';
        $domain = $this->config->get('session.domain') ?: $uri->getHost();
        $cookie = new Cookie('XSRF-TOKEN', $token, $this->getCookieExpire(), $path, $domain, $secure);
        if (!\method_exists($response, 'withCookie')) {
            return $response->withHeader('Set-Cookie', (string)$cookie);
        }
        return $response->withCookie($cookie);
    }
}