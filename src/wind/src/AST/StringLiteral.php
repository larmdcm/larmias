<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class StringLiteral implements ExpressionInterface
{
    public function __construct(protected Token $token, public string $value)
    {
    }

    public function expressionNode(): void
    {
        // TODO: Implement expressionNode() method.
    }

    public function tokenLiteral(): string
    {
        return $this->token->getLiteral();
    }

    public function toString(): string
    {
        return $this->token->getLiteral();
    }
}