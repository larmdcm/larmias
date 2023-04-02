<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class IfExpression implements ExpressionInterface
{
    public function __construct(protected Token        $token, public ?ExpressionInterface $condition = null,
                                public ?BlockStatement $consequence = null, public ?BlockStatement $alternative = null)
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
        $buffer = 'if';
        $buffer .= $this->condition->toString() . ' ';
        $buffer .= $this->consequence->toString();

        if ($this->alternative !== null) {
            $buffer .= 'else ';
            $buffer .= $this->alternative->toString();
        }
        return $buffer;
    }
}