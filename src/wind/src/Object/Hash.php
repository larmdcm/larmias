<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class Hash implements ObjectInterface
{
    /**
     * @param HashParis[] $pairs
     */
    public function __construct(public array $pairs = [])
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::HASH_OBJ);
    }

    public function inspect(): string
    {
        $paris = [];

        foreach ($this->pairs as $value) {
            $paris[] = sprintf('%s:%s', $value->key->inspect(), $value->value->inspect());
        }

        return '{' . implode(',', $paris) . '}';
    }
}