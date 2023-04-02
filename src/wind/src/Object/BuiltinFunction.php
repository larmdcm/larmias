<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class BuiltinFunction
{
    /**
     * @param callable $callable
     */
    public function __construct(protected mixed $callable)
    {
    }

    public function __invoke(): ?ObjectInterface
    {
        return call_user_func($this->callable, ...func_get_args());
    }
}