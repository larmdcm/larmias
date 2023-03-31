<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

interface ExpressionInterface extends NodeInterface
{
    /**
     * @return void
     */
    public function expressionNode(): void;
}