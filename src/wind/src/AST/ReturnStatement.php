<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class ReturnStatement implements StatementInterface
{
    public ?ExpressionInterface $returnValue = null;

    public function __construct(public Token $token)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->getLiteral();
    }

    public function toString(): string
    {
        $buffer = $this->tokenLiteral() . ' ';

        if ($this->returnValue !== null) {
            $buffer .= $this->returnValue->toString();
        }

        $buffer .= ';';

        return $buffer;
    }

    public function statementNode(): void
    {
        // TODO: Implement statementNode() method.
    }
}