<?php

declare(strict_types=1);

namespace Larmias\Auth\Traits;

use Larmias\Contracts\ContainerInterface;
use Larmias\Auth\AuthManager;
use Larmias\Auth\Facade\Auth;

/**
 * @property ContainerInterface $container
 */
trait Authenticate
{
    /**
     * @param AuthManager|null $authManager
     * @return void
     */
    public function setAuthManager(?AuthManager $authManager = null): void
    {
        if (!$authManager) {
            $authManager = $this->newAuthManager();
        }

        Auth::setAuthManager($authManager);
    }

    /**
     * @return AuthManager
     */
    public function newAuthManager(): AuthManager
    {
        /** @var AuthManager $authManager */
        $authManager = $this->container->make(AuthManager::class, [], true);
        return $authManager;
    }

    /**
     * @param mixed $parameter
     * @param array $guards
     * @return void
     */
    public function authenticate(mixed $parameter, array $guards): void
    {
        foreach ($guards as $name) {
            $guard = Auth::guard($name);
            if ($guard->guest()) {
                if ($identity = $guard->getAuthentication()->authenticate($parameter)) {
                    $guard->setUser($identity);
                }
            }
        }
    }
}