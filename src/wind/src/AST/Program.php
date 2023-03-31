<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

class Program implements NodeInterface
{
    /**
     * @var StatementInterface[]
     */
    public array $statements = [];

    public function tokenLiteral(): string
    {
        if (count($this->statements) > 0) {
            return $this->statements[0]->tokenLiteral();
        }

        return '';
    }

    public function toString(): string
    {
        $buffer = '';

        foreach ($this->statements as $statement) {
            $buffer .= $statement->toString();
        }

        return $buffer;
    }
}