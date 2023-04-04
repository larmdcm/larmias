<?php

declare(strict_types=1);

namespace Larmias\Auth\Middleware;

use Larmias\Auth\AuthManager;
use Larmias\Auth\Facade\Auth;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticateMiddleware implements MiddlewareInterface
{
    /**
     * @var array|null[]
     */
    protected array $guards = [null];

    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context)
    {
        Auth::setContext($this->context);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var AuthManager $authManager */
        $authManager = $this->container->make(AuthManager::class, [], true);
        Auth::setAuthManager($authManager);
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
            $guard = Auth::guard($name);
            if ($guard->guest()) {
                if ($identity = $guard->getAuthentication()->authenticate($request)) {
                    $guard->login($identity);
                }
            }
        }
    }
}