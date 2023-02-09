<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface LockerInterface
{
    public function acquire(): bool;

    public function block(?int $waitTimeout = null): bool;

    public function release(): bool;
}