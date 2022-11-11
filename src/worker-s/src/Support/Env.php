<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Support;

class Env
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function set(string $name,mixed $value): void
    {
        Arr::set(static::$data,$name,$value);
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public static function get(string $name,mixed $default = null)
    {
        return Arr::get(static::$data,$name,$default);
    }

    /**
     * @param array $data
     * @return void
     */
    public static function merge(array $data): void
    {
        static::$data = \array_merge(static::$data,$data);
    }
}