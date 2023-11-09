<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use Closure;
use InvalidArgumentException;
use Larmias\Stringable\Str;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionNamedType;
use Throwable;

class Parameter
{
    /** @var Closure|null */
    protected static ?Closure $globalMakeClassHandler = null;

    /**
     * ParameterBind constructor.
     *
     * @param ReflectionFunctionAbstract|null $reflect
     * @param Closure|null $makeClassHandler
     */
    public function __construct(protected ?ReflectionFunctionAbstract $reflect = null, protected ?Closure $makeClassHandler = null)
    {
    }

    /**
     * @param ...$args
     * @return array
     * @throws Throwable
     */
    public function __invoke(...$args): array
    {
        return $this->bind(...$args);
    }

    /**
     * @param ...$args
     * @return array
     * @throws Throwable
     */
    public function bind(...$args): array
    {
        if (is_null($this->reflect)) {
            throw new InvalidArgumentException('reflect is null.');
        }

        return $this->bindParams($this->reflect, static::getArgs(...$args));
    }

    /**
     * @param ReflectionFunctionAbstract $reflect
     * @param array $args
     * @return array
     * @throws Throwable
     */
    public function bindReflect(ReflectionFunctionAbstract $reflect, array $args = []): array
    {
        return $this->setReflect($reflect)->bind($args);
    }

    /**
     * @param ...$args
     * @return array
     */
    public static function getArgs(...$args): array
    {
        $vars = count($args) > 1 ? $args : ($args[0] ?? []);
        return (array)$vars;
    }

    /**
     * @param ReflectionFunctionAbstract $reflect
     * @param array $vars
     * @return array
     * @throws Throwable
     */
    protected function bindParams(ReflectionFunctionAbstract $reflect, array $vars = []): array
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }
        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();
        $args = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $lowerName = Str::snake($name);

            /** @var ReflectionNamedType $reflectionType */
            $reflectionType = $param->getType();

            if ($reflectionType && method_exists($reflectionType, 'isBuiltin') && $reflectionType->isBuiltin() === false) {
                $args[] = $this->getObjectParam($reflectionType->getName(), $vars, $param);
            } elseif ($type == 1 && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif ($type == 0 && array_key_exists($name, $vars)) {
                $args[] = $vars[$name];
            } elseif ($type == 0 && array_key_exists($lowerName, $vars)) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     *
     * @param string $className
     * @param array $vars
     * @param ReflectionParameter $parameter
     * @return object|null
     * @throws Throwable
     */
    protected function getObjectParam(string $className, array &$vars, ReflectionParameter $parameter): ?object
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className, $parameter);
        }

        return $result;
    }

    /**
     * @param string $className
     * @param ReflectionParameter $parameter
     * @return object|null
     * @throws Throwable
     */
    protected function make(string $className, ReflectionParameter $parameter): ?object
    {
        if ($this->makeClassHandler) {
            return call_user_func($this->makeClassHandler, $className, $parameter);
        } else if (static::$globalMakeClassHandler) {
            return call_user_func(static::$globalMakeClassHandler, $className, $parameter);
        }
        try {
            $reflect = ReflectionManager::reflectClass($className);
            $constructor = $reflect->getConstructor();
            $args = $constructor ? $this->bindParams($constructor) : [];
            return $reflect->newInstanceArgs($args);
        } catch (Throwable $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw $e;
        }
    }

    /**
     * @param Closure|null $globalMakeClassHandler
     */
    public static function setGlobalMakeClassHandler(?Closure $globalMakeClassHandler): void
    {
        static::$globalMakeClassHandler = $globalMakeClassHandler;
    }

    /**
     * @param Closure|null $makeClassHandler
     * @return self
     */
    public function setMakeClassHandler(?Closure $makeClassHandler): self
    {
        $this->makeClassHandler = $makeClassHandler;
        return $this;
    }

    /**
     * @return ReflectionFunctionAbstract
     */
    public function getReflect(): ReflectionFunctionAbstract
    {
        return $this->reflect;
    }

    /**
     * @param ReflectionFunctionAbstract $reflect
     * @return self
     */
    public function setReflect(ReflectionFunctionAbstract $reflect): self
    {
        $this->reflect = $reflect;
        return $this;
    }
}