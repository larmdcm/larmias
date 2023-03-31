<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

interface StatementInterface extends NodeInterface
{
    /**
     * @return void
     */
    public function statementNode(): void;
}