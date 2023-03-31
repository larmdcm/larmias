<?php

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class Identifier implements ExpressionInterface
{
    public function __construct(public Token $token, public string $value)
    {
    }

    public function expressionNode(): void
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->getLiteral();
    }

    public function toString(): string
    {
        return $this->value;
    }
}