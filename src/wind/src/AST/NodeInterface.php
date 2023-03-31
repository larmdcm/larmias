<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

interface NodeInterface
{
    /**
     * @return string
     */
    public function tokenLiteral(): string;

    /**
     * @return string
     */
    public function toString(): string;
}