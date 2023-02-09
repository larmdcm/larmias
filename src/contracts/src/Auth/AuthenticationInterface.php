<?php

declare(strict_types=1);

namespace Larmias\Contracts\Auth;

interface AuthenticationInterface
{
    /**
     * @param mixed $parameter
     * @return IdentityInterface|null
     */
    public function authenticate(mixed $parameter): ?IdentityInterface;
}