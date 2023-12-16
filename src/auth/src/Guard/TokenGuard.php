<?php

declare(strict_types=1);

namespace Larmias\Auth\Guard;

use Larmias\Contracts\Auth\IdentityInterface;

class TokenGuard extends Guard
{
    /**
     * @param IdentityInterface $identity
     * @return bool
     */
    public function login(IdentityInterface $identity): bool
    {
        $this->identity = $identity;
        return true;
    }

    /**
     * @param mixed|null $params
     * @return bool
     */
    public function logout(mixed $params = null): bool
    {
        $this->identity = null;
        return true;
    }
}