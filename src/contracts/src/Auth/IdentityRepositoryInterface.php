<?php

declare(strict_types=1);

namespace Larmias\Contracts\Auth;

interface IdentityRepositoryInterface
{
    /**
     * @param mixed $parameter
     * @return IdentityInterface|null
     */
    public function findIdentity(mixed $parameter): ?IdentityInterface;
}