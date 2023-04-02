<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class HashLiteral implements ExpressionInterface
{
    /**
     * @param Token $token
     * @param array $pairs
     */
    public function __construct(protected Token $token, public array $pairs = [])
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
        $pairs = [];

        foreach ($this->pairs as $map) {
            $pairs[] = $map['key']->toString() . ':' . $map['value']->toString();
        }
        return '{' . implode(',', $pairs) . '}';
    }
}