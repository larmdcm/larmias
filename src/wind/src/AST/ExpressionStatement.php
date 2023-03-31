<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class ExpressionStatement implements StatementInterface
{
    public function __construct(public Token $token, public ?ExpressionInterface $expression = null)
    {
    }

    public function statementNode(): void
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->getLiteral();
    }

    public function toString(): string
    {
        if ($this->expression !== null) {
            return $this->expression->toString();
        }

        return '';
    }
}