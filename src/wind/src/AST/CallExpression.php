<?php

declare(strict_types=1);

namespace Larmias\Wind\AST;

use Larmias\Wind\Lexer\Token;

class CallExpression implements ExpressionInterface
{
    /**
     * @param Token $token
     * @param ExpressionInterface|null $function
     * @param ExpressionInterface[] $arguments
     */
    public function __construct(protected Token $token, protected ?ExpressionInterface $function = null, protected array $arguments = [])
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
        $args = [];

        foreach ($this->arguments as $argument) {
            $args[] = $argument->toString();
        }

        $buffer .= $this->function->toString();
        $buffer .= '(';
        $buffer .= implode(', ', $args);
        $buffer .= ')';

        return $buffer;
    }
}