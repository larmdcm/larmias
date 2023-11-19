<?php

declare(strict_types=1);

namespace Larmias\Auth\Guard;

use Larmias\Contracts\Auth\IdentityInterface;
use Larmias\Contracts\SessionInterface;

class SessionGuard extends Guard
{
    /**
     * @var SessionInterface
     */
    protected SessionInterface $session;

    /**
     * @param SessionInterface $session
     * @return void
     */
    public function initialize(SessionInterface $session): void
    {
        $this->session = $session;
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
        $this->identity = null;
        return $this->session->delete($this->authName);
    }
}