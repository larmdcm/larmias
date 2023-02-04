<?php

declare(strict_types=1);

namespace Larmias\Session\Middleware;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\SessionInterface;
use Larmias\Http\Message\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public function __construct(protected SessionInterface $session, protected ConfigInterface $config)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sessionId = $request->getCookieParams()[$this->session->getName()] ?? ($request->getQueryParams()[$this->session->getName()] ?? '');
        if (!empty($sessionId) && $this->session->validId($sessionId)) {
            $this->session->setId($sessionId);
        } else {
            $this->session->setId($this->session->generateSessionId());
        }
        $this->session->start();
        try {
            $response = $handler->handle($request);
            return $this->addCookieResponse($request, $response);
        } finally {
            $this->session->save();
        }
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
        $uri = $request->getUri();
        $path = '/';
        $secure = \strtolower($uri->getScheme()) === 'https';

        $domain = $this->config->get('session.domain') ?: $uri->getHost();
        $cookie = new Cookie($this->session->getName(), $this->session->getId(), $this->getCookieExpire(), $path, $domain, $secure);
        if (!\method_exists($response, 'withCookie')) {
            return $response->withHeader('Set-Cookie', (string)$cookie);
        }
        return $response->withCookie($cookie);
    }
}