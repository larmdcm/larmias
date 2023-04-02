<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class FunctionLiteral implements ExpressionInterface
{
    /**
     * @param Token $token
     * @param Identifier[] $parameters
     * @param BlockStatement|null $body
     */
    public function __construct(protected Token $token, public array $parameters = [], public ?BlockStatement $body = null)
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
        $params = [];

        foreach ($this->parameters as $parameter) {
            $params[] = $parameter->toString();
        }

        $buffer .= $this->tokenLiteral();
        $buffer .= '(';
        $buffer .= implode(',', $params);
        $buffer .= ') ';
        $buffer .= $this->body->toString();

        return $buffer;
    }
}