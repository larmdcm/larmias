<?php

declare(strict_types=1);

namespace Larmias\Session\Middleware;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\SessionInterface;
use Larmias\Http\Message\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function time;
use function strtolower;
use function method_exists;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context, protected ConfigInterface $config)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sessionId = $request->getCookieParams()[$this->getSession()->getName()] ?? ($request->getQueryParams()[$this->getSession()->getName()] ?? '');
        if (!empty($sessionId) && $this->getSession()->validId($sessionId)) {
            $this->getSession()->setId($sessionId);
        } else {
            $this->getSession()->setId($this->getSession()->generateSessionId());
        }
        $this->getSession()->start();
        try {
            $response = $handler->handle($request);
            return $this->addCookieResponse($request, $response);
        } finally {
            $this->getSession()->save();
        }
    }

    /**
     * @return int
     */
    protected function getCookieExpire(): int
    {
        $lifeTime = $this->config->get('session.cookie_lifetime', 0);
        return $lifeTime > 0 ? time() + $lifeTime : 0;
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
        $secure = strtolower($uri->getScheme()) === 'https';

        $domain = $this->config->get('session.domain') ?: $uri->getHost();
        $cookie = new Cookie($this->getSession()->getName(), $this->getSession()->getId(), $this->getCookieExpire(), $path, $domain, $secure);
        if (!method_exists($response, 'withCookie')) {
            return $response->withHeader('Set-Cookie', (string)$cookie);
        }
        return $response->withCookie($cookie);
    }

    /**
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->context->remember(SessionInterface::class, function () {
            return $this->container->make(SessionInterface::class, [], true);
        });
    }
}