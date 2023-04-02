<?php

declare (strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class InfixExpression implements ExpressionInterface
{
    public function __construct(protected Token $token, public ExpressionInterface $left, public string $operator, public ?ExpressionInterface $right = null)
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
        $buffer = '(';
        $buffer .= $this->left->toString();
        $buffer .= ' ' . $this->operator . ' ';
        $buffer .= $this->right->toString();
        $buffer .= ')';
        return $buffer;
    }
}