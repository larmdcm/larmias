<?php

declare(strict_types=1);

namespace Larmias\Utils\Reflection;

use ReflectionException;
use ReflectionFunctionAbstract;

class Invoker
{
    /**
     * @var Parameter
     */
    protected Parameter $parameter;

    /**
     * ReflectionManager constructor.
     *
     * @param Parameter|null $parameter
     */
    public function __construct(?Parameter $parameter = null)
    {
        $this->parameter = $parameter ?: new Parameter();
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     *
     * @param string $class 类名
     * @param array $params 参数
     * @return object
     * @throws ReflectionException
     */
    public function invokeClass(string $class, array $params = []): object
    {
        return $this->makeClassInvoker($class, $params)->getInstance();
    }

    /**
     * @param string|array $method
     * @param array $params
     * @param bool $accessible
     * @return mixed
     * @throws ReflectionException
     */
    public function invokeMethod(string|array $method, array $params = [], bool $accessible = false): mixed
    {
        if (is_array($method)) {
            [$class, $method] = $method;
            $class = \is_object($class) ? $class : $this->invokeClass($class);
        } else {
            [$class, $method] = explode('::', $method);
        }

        $reflectMethod = \is_object($class) ? ReflectionManager::reflectMethod($class, $method) : ReflectionManager::reflectMethod($class, $method, true);
        $params = $this->bindParameter($reflectMethod, $params);
        if (\is_object($class)) {
            return ClassInvoker::invokeMethod([$class, $reflectMethod], $params, $accessible);
        }
        return StaticClassInvoker::invokeMethod($reflectMethod, $params, $accessible);
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     *
     * @param callable $function 函数或者闭包
     * @param array $params 参数
     * @return mixed
     * @throws ReflectionException
     */
    public function invokeFunction(callable $function, array $params = []): mixed
    {
        $invoker = FunctionInvoker::new($function);
        $args = $this->bindParameter($invoker->getReflect(), $params);
        return $invoker->invoke($args);
    }

    /**
     * @param string|object $object
     * @param array $params
     * @return ClassInvoker
     * @throws ReflectionException
     */
    protected function makeClassInvoker(string|object $object, array $params = []): ClassInvoker
    {
        $class = \is_string($object) ? $object : \get_class($object);
        return new ClassInvoker($object, function (ReflectionFunctionAbstract $abstract) use ($params) {
            return $this->bindParameter($abstract, $params);
        });
    }

    /**
     * @param ReflectionFunctionAbstract $abstract
     * @param array $params
     * @return array
     * @throws ReflectionException
     */
    public function bindParameter(ReflectionFunctionAbstract $abstract, array $params = []): array
    {
        return $this->parameter->bindReflect($abstract, $params);
    }

    /**
     * @return Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * @param Parameter $parameter
     * @return self
     */
    public function setParameter(Parameter $parameter): self
    {
        $this->parameter = $parameter;
        return $this;
    }
}