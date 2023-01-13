<?php

declare(strict_types=1);

namespace Larmias\Di\Invoker;

use Closure;

abstract class AbstractInvokeResolver
{
    /**
     * @var array
     */
    public array $classes = [];

    /**
     * @var array
     */
    public array $annotations = [];

    public function process(array $args, Closure $process): mixed
    {
        return $process($args);
    }
}