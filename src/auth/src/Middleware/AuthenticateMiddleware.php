<?php

declare(strict_types=1);

namespace Larmias\Auth\Middleware;

use Larmias\Auth\AuthManager;
use Larmias\Auth\Exceptions\AuthenticationException;
use Larmias\Auth\Facade\Auth;
use Larmias\Contracts\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticateMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthManager
     */
    protected AuthManager $authManager;

    /**
     * @var array|null[]
     */
    protected array $guards = [null];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        /** @var AuthManager $authManager */
        $authManager = $this->container->make(AuthManager::class, [], true);
        $this->authManager = $authManager;
        Auth::setAuthManager($authManager);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->authenticate($request, $this->guards);
        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $guards
     * @return void
     */
    protected function authenticate(ServerRequestInterface $request, array $guards): void
    {
        foreach ($guards as $name) {
            $guard = $this->authManager->guard($name);
            if ($guard->guest()) {
                if ($identity = $guard->getAuthentication()->authenticate($request)) {
                    $guard->login($identity);
                } else {
                    $this->unauthenticated($request, $name);
                }
            }
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param string|null $name
     * @return void
     */
    protected function unauthenticated(ServerRequestInterface $request, ?string $name = null): void
    {
        throw new AuthenticationException('Unauthenticated.', $name);
    }
}