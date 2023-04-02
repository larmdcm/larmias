<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class ArrayObject implements ObjectInterface
{
    /**
     * @param ObjectInterface[] $elements
     */
    public function __construct(public array $elements = [])
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::ARRAY_OBJ);
    }

    public function inspect(): string
    {
        return '[' . implode(',', array_map(fn($element) => $element->inspect(), $this->elements)) . ']';
    }
}