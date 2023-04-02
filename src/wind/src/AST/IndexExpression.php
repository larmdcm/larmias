<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class IndexExpression implements ExpressionInterface
{
    public function __construct(protected Token $token, public ?ExpressionInterface $left = null, public ?ExpressionInterface $index = null)
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
        $buffer .= '[';
        $buffer .= $this->index->toString();
        $buffer .= '])';
        return $buffer;
    }
}