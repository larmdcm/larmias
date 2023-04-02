<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class ArrayLiteral implements ExpressionInterface
{
    /**
     * @param Token $token
     * @param ExpressionInterface[] $elements
     */
    public function __construct(protected Token $token, public array $elements = [])
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
        $buffer = '';
        $elements = [];
        foreach ($this->elements as $element) {
            $elements[] = $element->toString();
        }

        $buffer .= '[';
        $buffer .= implode(',', $elements);
        $buffer .= ']';

        return $buffer;
    }
}