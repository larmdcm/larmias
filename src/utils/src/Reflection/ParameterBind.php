<?php

declare(strict_types=1);

namespace Larmias\Utils\Reflection;

use Larmias\Utils\Str;

use ReflectionClass;
use ReflectionFunctionAbstract;
use InvalidArgumentException;
use Closure;

class ParameterBind
{
    /** @var Closure|null  */
    protected static ?Closure $globalMakeClassHandler = null;

    /**
     * ParameterBind constructor.
     *
     * @param ReflectionFunctionAbstract|null $reflect
     * @param Closure|null $makeClassHandler
     */
    public function __construct(protected ?ReflectionFunctionAbstract $reflect = null,protected ?Closure $makeClassHandler = null)
    {
    }
    
    /**
     * @param ...$args
     * @return array
     * @throws \ReflectionException
     */
    public function __invoke(...$args): array
    {
        return $this->invoke(...$args);
    }

    /**
     * @param ...$args
     * @return array
     * @throws \ReflectionException
     */
    public function invoke(...$args): array
    {
        if (is_null($this->reflect)) {
            throw new InvalidArgumentException('reflect is null.');
        }
        return $this->bindParams($this->reflect,static::getArgs(...$args));
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
     * @param array $vars
     * @return array
     * @throws \ReflectionException
     */
    protected function bindParams(ReflectionFunctionAbstract $reflect,array $vars = []): array
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }
        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();
        $args   = [];

        foreach ($params as $param) {
            $name           = $param->getName();
            $lowerName      = Str::snake($name);

            /** @var \ReflectionNamedType $reflectionType */
            $reflectionType = $param->getType();

            if ($reflectionType && $reflectionType->isBuiltin() === false) {
                $args[] = $this->getObjectParam($reflectionType->getName(), $vars);
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
     * @param string $className 类名
     * @param array $vars 参数
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getObjectParam(string $className, array &$vars): mixed
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * @throws \ReflectionException
     */
    protected function make(string $className): object
    {
        if ($this->makeClassHandler) {
            return call_user_func($this->makeClassHandler,$className);
        } else if (static::$globalMakeClassHandler) {
            return call_user_func(static::$globalMakeClassHandler,$className);
        }
        $reflect = new ReflectionClass($className);
        $constructor = $reflect->getConstructor();
        $args = $constructor ? $this->bindParams($constructor) : [];
        return $reflect->newInstanceArgs($args);
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
     */
    public function setMakeClassHandler(?Closure $makeClassHandler): void
    {
        $this->makeClassHandler = $makeClassHandler;
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