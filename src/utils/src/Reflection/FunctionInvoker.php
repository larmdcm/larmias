<?php

declare(strict_types=1);

namespace Larmias\Utils\Reflection;

use ReflectionFunction;

class FunctionInvoker
{
    /** @var callable */
    protected $function;

    /** @var ReflectionFunction  */
    protected ReflectionFunction $reflect;

    /** @var ParameterBind  */
    protected ParameterBind $parameterBind;

    /**
     * FunctionInvoker constructor.
     *
     * @param callable $function
     * @throws \ReflectionException
     */
    public function __construct(callable $function)
    {
        $this->function = $function;
        $this->reflect = new ReflectionFunction($function);
        $this->parameterBind = (new ParameterBind())->setReflect($this->reflect);
    }

    /**
     * @param ...$args
     * @return mixed
     * @throws \ReflectionException
     */
    public function invoke(...$args): mixed
    {
        return call_user_func_array($this->function,$this->parameterBind->invoke(...$args));
    }
}