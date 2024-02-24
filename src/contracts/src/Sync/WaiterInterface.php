<?php

declare(strict_types=1);

namespace Larmias\Contracts\Sync;

use Closure;

interface WaiterInterface
{
    /**
     * @param Closure $closure
     * @param float|null $timeout
     * @return mixed
     */
    public function wait(Closure $closure, ?float $timeout = null): mixed;
}