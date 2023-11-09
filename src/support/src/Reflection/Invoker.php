<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use Closure;
use ReflectionException;
use ReflectionFunctionAbstract;
use Throwable;

class Invoker
{
    /**
     * @var string
     */
    public const INVOKE_METHOD = 'method';

    /**
     * @var string
     */
    public const INVOKE_STATIC_METHOD = 'staticMethod';

    /**
     * @var Parameter
     */
    protected Parameter $parameter;

    /**
     * 调用回调
     *
     * @var array
     */
    protected array $resolveCallback = [
        self::INVOKE_METHOD => [],
        self::INVOKE_STATIC_METHOD => [],
    ];

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
     * 注册一个调用回调
     *
     * @param string|array $type
     * @param Closure $callback
     * @return void
     */
    public function resolving(string|array $type, Closure $callback): void
    {
        foreach ((array)$type as $item) {
            $this->resolveCallback[$item] = $callback;
        }
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
     * @throws Throwable
     */
    public function invokeMethod(string|array $method, array $params = [], bool $accessible = false): mixed
    {
        if (is_array($method)) {
            [$class, $method] = $method;
            $class = is_object($class) ? $class : $this->invokeClass($class);
        } else {
            [$class, $method] = explode('::', $method);
        }
        $isObject = is_object($class);
        $reflectMethod = $isObject ? ReflectionManager::reflectMethod($class, $method) : ReflectionManager::reflectMethod($class, $method, true);
        $params = $this->bindParameter($reflectMethod, $params);
        $type = $isObject ? self::INVOKE_METHOD : self::INVOKE_STATIC_METHOD;
        return $this->invoke(
            $type,
            fn() => $isObject ? ClassInvoker::invokeMethod([$class, $reflectMethod], $params, $accessible)
                : StaticClassInvoker::invokeMethod($reflectMethod, $params, $accessible),
            ['type' => $type, 'class' => $isObject ? get_class($class) : $class, 'method' => $method, 'parameter' => $params]
        );
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     *
     * @param callable $function 函数或者闭包
     * @param array $params 参数
     * @return mixed
     * @throws Throwable
     */
    public function invokeFunction(callable $function, array $params = []): mixed
    {
        $invoker = FunctionInvoker::new($function);
        $args = $this->bindParameter($invoker->getReflect(), $params);
        return $invoker->invoke($args);
    }

    /**
     * @param string $type
     * @param Closure $handler
     * @param array $args
     * @return mixed
     */
    protected function invoke(string $type, Closure $handler, array $args = []): mixed
    {
        if (!isset($this->resolveCallback[$type])) {
            return $handler();
        }
        return call_user_func($this->resolveCallback[$type], $handler, $args);
    }

    /**
     * @param string|object $object
     * @param array $params
     * @return ClassInvoker
     * @throws ReflectionException
     */
    protected function makeClassInvoker(string|object $object, array $params = []): ClassInvoker
    {
        return new ClassInvoker($object, function (ReflectionFunctionAbstract $abstract) use ($params) {
            return $this->bindParameter($abstract, $params);
        });
    }

    /**
     * @param ReflectionFunctionAbstract $abstract
     * @param array $params
     * @return array
     * @throws Throwable
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