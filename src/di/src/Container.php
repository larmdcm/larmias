<?php

declare(strict_types=1);

namespace Larmias\Di;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Annotation\Scope;
use Larmias\Di\Invoker\InvokeResolver;
use Larmias\Support\Reflection\Invoker;
use Larmias\Support\Reflection\Parameter;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionException;
use ReflectionParameter;
use Throwable;
use Traversable;
use function count;
use function current;
use function get_class;
use function is_array;
use function is_object;
use function is_string;

class Container implements ContainerInterface, ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var ContainerInterface|null
     */
    protected static ?ContainerInterface $instance = null;

    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * @var array
     */
    protected array $instances = [];

    /**
     * 容器回调
     * @var array
     */
    protected array $invokeCallback = [];

    /**
     * @var Invoker
     */
    protected Invoker $invoker;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->invoker = new Invoker(new Parameter(makeClassHandler: function (string $className, ReflectionParameter $parameter) {
            try {
                return $this->make($className);
            } catch (Throwable $e) {
                if ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }
                throw $e;
            }
        }));

        $this->invoker->resolving(Invoker::INVOKE_METHOD, function (Closure $process, array $args) {
            if (InvokeResolver::isEmpty()) {
                return $process();
            }
            return InvokeResolver::process($process, $args);
        });

        $this->bind([
            self::class => $this,
            ContainerInterface::class => $this,
            PsrContainerInterface::class => $this,
        ]);
    }

    /**
     * @return ContainerInterface
     */
    public static function getInstance(): ContainerInterface
    {
        if (is_null(static::$instance)) {
            static::setInstance(new static());
        }
        return static::$instance;
    }

    /**
     * @param ContainerInterface|null $container
     * @return void
     */
    public static function setInstance(ContainerInterface $container = null): void
    {
        static::$instance = $container;
    }

    /**
     * 注册容器对象回调
     * @param string|Closure $abstract
     * @param Closure|null $callback
     * @return void
     */
    public function resolving(string|Closure $abstract, Closure $callback = null): void
    {
        if ($abstract instanceof Closure) {
            $this->invokeCallback['*'][] = $abstract;
            return;
        }

        $abstract = $this->getAlias($abstract);

        $this->invokeCallback[$abstract][] = $callback;
    }

    /**
     * 创建类实例
     * @param string $abstract
     * @param array $params
     * @param boolean $newInstance
     * @return mixed
     * @throws Throwable
     */
    public function make(string $abstract, array $params = [], bool $newInstance = false): mixed
    {
        $abstract = $this->getAlias($abstract);
        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract] instanceof Closure) {
            $object = $this->invokeFunction($this->bindings[$abstract], $params);
        } else {
            $object = $this->invokeClass($abstract, $params);
        }

        $scope = $this->getScope($object);

        if ($scope) {
            $newInstance = $scope->type === Scope::PROTOTYPE;
        }

        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 获取对象实例化作用域
     * @param string|object $object
     * @return Scope|null
     */
    protected function getScope(string|object $object): ?Scope
    {
        $class = is_object($object) ? get_class($object) : $object;
        $annotations = AnnotationCollector::get(sprintf('%s.%s.%s', $class, 'class', Scope::class));
        if (empty($annotations)) {
            return null;
        }
        return current($annotations);
    }

    /**
     * 绑定类、闭包、实例、接口实现到容器
     * @param string|array $abstract
     * @param mixed|null $concrete
     * @param bool $force
     * @return ContainerInterface
     */
    public function bind(string|array $abstract, mixed $concrete = null, bool $force = true): ContainerInterface
    {
        if (!$concrete) {
            $concrete = $abstract;
        }
        if (is_array($abstract)) {
            foreach ($abstract as $key => $value) {
                $this->bind($key, $value, $force);
            }
        } else if (is_object($concrete) && !($concrete instanceof Closure)) {
            $this->instance($abstract, $concrete, $force);
        } else {
            $alias = $this->getAlias($abstract);
            if (($alias === $abstract && !isset($this->bindings[$alias])) || $force) {
                if ($concrete instanceof Closure || $alias !== $concrete) {
                    $this->bindings[$alias] = $concrete;
                }
            }
        }
        return $this;
    }

    /**
     * 不存在则绑定类、闭包、实例、接口实现到容器
     * @param array|string $abstract
     * @param mixed $concrete
     * @return ContainerInterface
     */
    public function bindIf(array|string $abstract, mixed $concrete = null): ContainerInterface
    {
        return $this->bind($abstract, $concrete, false);
    }

    /**
     * 绑定一个类实例到容器
     * @param string $abstract
     * @param object $instance
     * @param bool $force
     * @return object
     */
    public function instance(string $abstract, object $instance, bool $force = true): object
    {
        $alias = $this->getAlias($abstract);
        if (($alias === $abstract && !isset($this->instances[$alias])) || $force) {
            $this->instances[$alias] = $instance;
        }
        return $instance;
    }

    /**
     * 获取类别名的真实类名
     * @param string $abstract
     * @return string
     */
    public function getAlias(string $abstract): string
    {
        if (isset($this->bindings[$abstract])) {
            $name = $this->bindings[$abstract];
            if (is_string($name)) {
                return $this->getAlias($name);
            }
        }
        return $abstract;
    }

    /**
     * 获取容器中的对象实例
     * @param string $id
     * @return object
     * @throws Throwable
     */
    public function get(string $id): object
    {
        return $this->make($id);
    }

    /**
     * 判断容器中是否存在类及标识
     * @param string $id
     * @return boolean
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /**
     * 判断容器中是否存在对象实例
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function exists(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->instances[$abstract]);
    }

    /**
     * 解绑容器中的对象实例
     * @param string $name
     * @return bool
     */
    public function unbind(string $name): bool
    {
        $name = $this->getAlias($name);

        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
        return true;
    }

    /**
     * 调用反射执行callable
     * @param mixed $callable
     * @param array $params
     * @param bool $accessible
     * @return mixed
     * @throws Throwable
     */
    public function invoke(mixed $callable, array $params = [], bool $accessible = false): mixed
    {
        if ($callable instanceof Closure || (is_string($callable) && !str_contains($callable, '::'))) {
            return $this->invokeFunction($callable, $params);
        }

        return $this->invokeMethod($callable, $params, $accessible);
    }

    /**
     * 执行函数或者闭包方法
     * @param callable $function
     * @param array $params
     * @return mixed
     * @throws Throwable
     */
    public function invokeFunction(callable $function, array $params = []): mixed
    {
        return $this->invoker->invokeFunction($function, $params);
    }

    /**
     * 调用反射执行类的实例化
     * @param string $class
     * @param array $params
     * @return object
     * @throws ReflectionException
     */
    public function invokeClass(string $class, array $params = []): object
    {
        $object = $this->invoker->invokeClass($class, $params);
        $this->invokeClassAfter($class, $object);
        return $object;
    }

    /**
     * 执行invokeClass回调
     * @param string $class 对象类名
     * @param object $object 容器对象实例
     * @return void
     */
    protected function invokeClassAfter(string $class, object $object): void
    {
        if (isset($this->invokeCallback['*'])) {
            foreach ($this->invokeCallback['*'] as $callback) {
                $callback($object, $this);
            }
        }

        if (isset($this->invokeCallback[$class])) {
            foreach ($this->invokeCallback[$class] as $callback) {
                $callback($object, $this);
            }
        }
    }

    /**
     * 调用反射执行类的方法
     * @param string|array $method
     * @param array $params
     * @param bool $accessible
     * @return mixed
     * @throws Throwable
     */
    public function invokeMethod(string|array $method, array $params = [], bool $accessible = false): mixed
    {
        return $this->invoker->invokeMethod($method, $params, $accessible);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->bind($name, $value);
    }

    /**
     * @param string $name
     * @return object
     * @throws Throwable
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->exists($name);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name)
    {
        $this->unbind($name);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->exists($offset);
    }

    /**
     * @throws Throwable
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->bind($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->unbind($offset);
    }

    //Countable
    public function count(): int
    {
        return count($this->instances);
    }

    //IteratorAggregate
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->instances);
    }
}