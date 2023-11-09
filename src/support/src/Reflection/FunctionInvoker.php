<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use ReflectionFunction;
use ReflectionException;

class FunctionInvoker
{
    /** @var callable */
    protected $function;

    /** @var ReflectionFunction */
    protected ReflectionFunction $reflect;

    /**
     * FunctionInvoker constructor.
     *
     * @param callable $function
     * @throws ReflectionException
     */
    public function __construct(callable $function)
    {
        $this->function = $function;
        $this->reflect = new ReflectionFunction($function);
    }

    /**
     * @param callable $function
     * @return FunctionInvoker
     * @throws ReflectionException
     */
    public static function new(callable $function): FunctionInvoker
    {
        return new static($function);
    }

    /**
     * @param array $args
     * @return mixed
     */
    public function invoke(array $args = []): mixed
    {
        return call_user_func_array($this->function, $args);
    }

    /**
     * @return ReflectionFunction
     */
    public function getReflect(): ReflectionFunction
    {
        return $this->reflect;
    }
}