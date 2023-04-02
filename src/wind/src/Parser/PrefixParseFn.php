<?php

namespace Larmias\Wind\Parser;

use Larmias\Wind\AST\ExpressionInterface;

class PrefixParseFn
{
    /**
     * @param callable $callable
     */
    public function __construct(protected mixed $callable)
    {

    }

    public function __invoke(): ?ExpressionInterface
    {
        return call_user_func($this->callable);
    }
}