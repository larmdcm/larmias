<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class LetStatement implements StatementInterface
{
    public Token $token;

    public Identifier $name;

    public ?ExpressionInterface $value = null;

    public function tokenLiteral(): string
    {
        return $this->token->getLiteral();
    }

    public function toString(): string
    {
        $buffer = '';
        $buffer = $buffer . $this->tokenLiteral() . ' ';
        $buffer .= $this->name->toString();
        $buffer .= ' = ';

        if ($this->value !== null) {
            $buffer .= $this->value->toString();
        }

        $buffer .= ';';

        return $buffer;
    }

    public function statementNode(): void
    {
        // TODO: Implement statementNode() method.
    }
}