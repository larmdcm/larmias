<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

class Context
{
    protected static int $id = 0;

    protected static array $data = [];


    public static function setData(string $name, mixed $value): void
    {
        $id = static::getId();
        if (!isset(static::$data[$id])) {
            static::$data[$id] = [];
        }
        static::$data[$id][$name] = $value;
    }

    public static function getData(string $name, mixed $default = null): mixed
    {
        $id = static::getId();
        $data = static::$data[$id];
        return $data[$name] ?? $default;
    }

    public static function setId(int $id): void
    {
        static::$id = $id;
    }

    public static function getId(): int
    {
        return static::$id;
    }
}