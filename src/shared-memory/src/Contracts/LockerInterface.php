<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

use Closure;

interface LockerInterface
{
    /**
     * @param Closure $handler
     * @return mixed
     */
    public function tryLock(Closure $handler): mixed;
}