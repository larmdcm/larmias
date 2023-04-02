<?php

namespace Larmias\Wind\Parser;

namespace Larmias\Wind\Parser;

use Larmias\Wind\AST\ExpressionInterface;

class InfixParseFn
{
    /**
     * @param callable $callable
     */
    public function __construct(protected mixed $callable)
    {
    }

    public function __invoke(?ExpressionInterface $expression): ?ExpressionInterface
    {
        return call_user_func($this->callable, $expression);
    }
}