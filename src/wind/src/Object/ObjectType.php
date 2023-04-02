<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class ObjectType
{
    public const INTEGER_OBJ = 'INTEGER';

    public const BOOLEAN_OBJ = 'BOOLEAN';

    public const NIL_OBJ = 'NIL';

    public const RETURN_VALUE_OBJ = 'RETURN_VALUE';

    public const ERROR_OBJ = 'ERROR';

    public const FUNCTION_OBJ = 'FUNCTION';

    public const STRING_OBJ = 'STRING';

    public const BUILTIN_OBJ = 'BUILTIN';

    public const ARRAY_OBJ = 'ARRAY';

    public const HASH_OBJ = 'HASH';

    public function __construct(protected string $value)
    {
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(string|ObjectInterface $value): bool
    {
        if ($value instanceof ObjectInterface) {
            return $this->value === $value->getType()->getValue();
        }

        return $this->value === $value;
    }
}