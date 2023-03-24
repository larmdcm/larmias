<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface LockerInterface
{
    /**
     * @return bool
     */
    public function acquire(): bool;

    /**
     * @param int|null $waitTimeout
     * @return bool
     */
    public function block(?int $waitTimeout = null): bool;

    /**
     * @return bool
     */
    public function release(): bool;
}