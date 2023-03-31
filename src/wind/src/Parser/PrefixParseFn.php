<?php

namespace Larmias\Wind\Parser;

use Larmias\Wind\AST\ExpressionInterface;

class PrefixParseFn
{
    public function __construct(protected \Closure $closure)
    {
    }

    public function __invoke(): ?ExpressionInterface
    {
        return call_user_func($this->closure);
    }
}