<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Closure;

interface TaskExecutorInterface
{
    /**
     * @param string|array|Closure $handler
     * @param array $args
     * @return bool
     */
    public function execute(string|array|Closure $handler, array $args = []): bool;
}