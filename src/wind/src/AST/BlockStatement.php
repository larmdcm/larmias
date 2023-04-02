<?php

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class BlockStatement implements ExpressionInterface
{
    /**
     * @param Token $token
     * @param StatementInterface[] $statements
     */
    public function __construct(protected Token $token, public array $statements = [])
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

        foreach ($this->statements as $statement) {
            $buffer .= $statement->toString();
        }
        return $buffer;
    }
}