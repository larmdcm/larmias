<?php

namespace Larmias\Wind\AST;

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class PrefixExpression implements ExpressionInterface
{
    public function __construct(protected Token $token, public string $operator, public ?ExpressionInterface $right = null)
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
        $buffer .= $this->operator;
        $buffer .= $this->right->toString();
        $buffer .= ')';

        return $buffer;
    }
}