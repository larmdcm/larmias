<?php

declare(strict_types=1);

namespace Larmias\Auth\Guard;

use Larmias\Contracts\Auth\AuthenticationInterface;
use Larmias\Contracts\Auth\IdentityInterface;
use Larmias\Contracts\Auth\IdentityRepositoryInterface;
use Larmias\Contracts\SessionInterface;

class SessionGuard extends Guard
{
    /**
     * @param SessionInterface $session
     * @param IdentityRepositoryInterface $repository
     * @param AuthenticationInterface $authentication
     */
    public function __construct(protected SessionInterface $session, protected IdentityRepositoryInterface $repository, protected AuthenticationInterface $authentication)
    {
    }

    /**
     * @param IdentityInterface $identity
     * @return bool
     */
    public function login(IdentityInterface $identity): bool
    {
        $this->identity = $identity;
        return $this->session->set($this->authName, $this->id());
    }

    /**
     * @return bool
     */
    public function logout(): bool
    {
        if ($this->guest()) {
            return false;
        }
        $this->identity = null;
        return $this->session->delete($this->authName);
    }
}