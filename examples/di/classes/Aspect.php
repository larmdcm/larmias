<?php

namespace Di;

use Larmias\Di\Invoker\AbstractInvokeResolver;
use Larmias\Di\Annotation\Invoke;

use Closure;

#[Invoke]
class Aspect extends AbstractInvokeResolver
{
    public array $classes = [
        A::class,
    ];

    public function process(array $args, Closure $process): mixed
    {
        return $process($args);
    }
}