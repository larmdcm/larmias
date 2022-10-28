<?php

declare(strict_types=1);

namespace ConciseS\Container;

use ConciseS\Contracts\ContainerInterface;
use Closure;
use LogicException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ConciseS\Support\Str;
use Traversable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Countable;
use ReflectionMethod;

class Container implements ContainerInterface,ArrayAccess,IteratorAggregate,Countable
{
    /**
     * @var ContainerInterface
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
     * @return ContainerInterface
     */
    public static function getInstance(): ContainerInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param  ContainerInterface|null $container
     * @return ContainerInterface|null
     */
    public static function setInstance(ContainerInterface $container = null): ?ContainerInterface
    {
        return static::$instance = $container;
    }

    /**
     * 创建类实例
     *
     * @param string  $abstract
     * @param array   $vars
     * @param boolean $newInstance
     * @return mixed
     */
    public function make(string $abstract,array $vars = [],bool $newInstance = false)
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract] instanceof Closure) {
            $object = $this->invokeFunction($this->bindings[$abstract], $vars);
        } else {
            $object = $this->invokeClass($abstract, $vars);
        }

        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 绑定类、闭包、实例、接口实现到容器
     *
     * @param string|array                $abstract
     * @param Closure|string|object|null  $concrete
     * @return void
     */
    public function bind(string|array $abstract,$concrete = null): void
    {
        if (!$concrete) {
            $concrete = $abstract;
        }
        if (is_array($abstract)) {
            foreach ($abstract as $key => $value) {
                $this->bind($key,$value);
            }
        } else if ($concrete instanceof Closure) {
            $abstract = $this->getAlias($abstract);
            $this->bindings[$abstract] = $concrete;
        } else if (is_object($concrete)) {
            $this->instance($abstract,$concrete);
        } else {
            $abstract = $this->getAlias($abstract);
            if ($abstract != $concrete) {
                $this->bindings[$abstract] = $concrete;
            }
        }
    }

    /**
     * 绑定一个类实例到容器
     *
     * @param string $abstract
     * @param object $instance
     * @return void
     */
    public function instance(string $abstract,object $instance): void
    {
        $abstract = $this->getAlias($abstract);
        $this->instances[$abstract] = $instance;
    }

    /**
     * 获取类别名的真实类名
     *
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
     * @param string $abstract
     * @return boolean
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * 获取容器中的对象实例
     *
     * @param string $id
     *
     * @throws LogicException
     * @return mixed
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->make($id);
        }
        throw new LogicException("{$id} not bound to container.");
    }

    /**
     * 判断容器中是否存在类及标识
     *
     * @param  string $id
     * @return boolean
     */
    public function has(string $id): bool
    {
        return $this->bound($id);
    }

    /**
     * 判断容器中是否存在对象实例
     *
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function exists(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->instances[$abstract]);
    }

    /**
     * 删除容器中的对象实例
     *
     * @param string $name
     * @return void
     */
    public function delete(string $name): void
    {
        $name = $this->getAlias($name);

        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
    }

    /**
     * 调用反射执行callable 支持参数绑定
     *
     * @param mixed $callable
     * @param array $vars       参数
     * @param bool  $accessible 设置是否可访问
     * @return mixed
     */
    public function invoke($callable, array $vars = [], bool $accessible = false)
    {
        if ($callable instanceof Closure) {
            return $this->invokeFunction($callable, $vars);
        } elseif (is_string($callable) && false === strpos($callable, '::')) {
            return $this->invokeFunction($callable, $vars);
        } else {
            return $this->invokeMethod($callable, $vars, $accessible);
        }
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     *
     * @param string|Closure $function 函数或者闭包
     * @param array          $vars     参数
     * @return mixed
     */
    public function invokeFunction($function, array $vars = [])
    {
        try {
            $reflect = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            throw new LogicException("function not exists: {$function}()");
        }

        $args = $this->bindParams($reflect, $vars);

        return $function(...$args);
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     *
     * @param string $class 类名
     * @param array  $vars  参数
     * @return mixed
     */
    public function invokeClass(string $class, array $vars = [])
    {
        try {
            $reflect = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw $e;
        }

        $constructor = $reflect->getConstructor();

        $args = $constructor ? $this->bindParams($constructor, $vars) : [];

        $object = $reflect->newInstanceArgs($args);

        return $object;
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     *
     * @param mixed $method     方法
     * @param array $vars       参数
     * @param bool  $accessible 设置是否可访问
     * @return mixed
     */
    public function invokeMethod($method, array $vars = [], bool $accessible = false)
    {
        if (is_array($method)) {
            [$class, $method] = $method;

            $class = is_object($class) ? $class : $this->invokeClass($class);
        } else {
            // 静态方法
            [$class, $method] = explode('::', $method);
        }

        try {
            $reflect = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;
            throw new LogicException('method not exists: ' . $class . '::' . $method . '()');
        }

        $args = $this->bindParams($reflect, $vars);

        if ($accessible) {
            $reflect->setAccessible($accessible);
        }

        return $reflect->invokeArgs(is_object($class) ? $class : null, $args);
    }

    /**
     * 绑定参数
     *
     * @param ReflectionFunctionAbstract $reflect 反射类
     * @param array                      $vars    参数
     * @return array
     */
    protected function bindParams(ReflectionFunctionAbstract $reflect, array $vars = []): array
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
            $reflectionType = $param->getType();

            if ($reflectionType && $reflectionType->isBuiltin() === false) {
                $args[] = $this->getObjectParam($reflectionType->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif (0 == $type && array_key_exists($name, $vars)) {
                $args[] = $vars[$name];
            } elseif (0 == $type && array_key_exists($lowerName, $vars)) {
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
     * @param array  $vars      参数
     * @return mixed
     */
    protected function getObjectParam(string $className, array &$vars)
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

    public function __set($name, $value)
    {
        $this->bind($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name): bool
    {
        return $this->exists($name);
    }

    public function __unset($name)
    {
        $this->delete($name);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($key): bool
    {
        return $this->exists($key);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        $this->delete($key);
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