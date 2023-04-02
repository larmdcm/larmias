<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

use Larmias\Wind\AST\BlockStatement;
use Larmias\Wind\AST\Identifier;
use Larmias\Wind\Environment\Environment;

class FunctionObject implements ObjectInterface
{
    /**
     * @param Identifier[] $parameters
     * @param BlockStatement|null $body
     * @param Environment|null $env
     */
    public function __construct(public array $parameters = [], public ?BlockStatement $body = null, public ?Environment $env = null)
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::FUNCTION_OBJ);
    }

    public function inspect(): string
    {
        $buffer = '';
        $params = [];

        foreach ($this->parameters as $parameter) {
            $params[] = $parameter->toString();
        }

        $buffer .= 'fn(';
        $buffer .= implode(',', $params);
        $buffer .= ') {' . PHP_EOL;
        $buffer .= $this->body->toString();
        $buffer .= PHP_EOL . '}';

        return $buffer;
    }
}