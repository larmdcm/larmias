<?php

declare(strict_types=1);

namespace Larmias\Contracts\Auth;

interface IdentityInterface
{
    /**
     * @return string|int
     */
    public function getId(): string|int;
}