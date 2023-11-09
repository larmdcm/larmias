<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use ReflectionClass;
use ReflectionException;

class ClassInvoker
{
    /** @var object */
    protected object $instance;

    /** @var ReflectionClass */
    protected ReflectionClass $reflect;

    /** @var string */
    protected string $className;

    /**
     * ClassInvoker constructor.
     *
     * @param string|object $object
     * @param callable|null $resolve
     * @throws ReflectionException
     */
    public function __construct(string|object $object, ?callable $resolve = null)
    {
        $this->reflect = ReflectionManager::reflectClass($object);
        $this->instance = is_object($object) ? $object : $this->newInstanceArgs($resolve);
        $this->className = get_class($this->instance);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws ReflectionException
     */
    public function __get(string $name): mixed
    {
        $property = ReflectionManager::reflectProperty($this->instance, $name);

        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }

        return $property->getValue($this->instance);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws ReflectionException
     */
    public function __set(string $name, mixed $value)
    {
        $property = ReflectionManager::reflectProperty($this->instance, $name);

        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }

        $property->setValue($this->instance, $value);
    }

    /**
     * @param string $method
     * @param array $args
     * @param bool $accessible
     * @return mixed
     * @throws ReflectionException
     */
    public function invoke(string $method, array $args = [], bool $accessible = false): mixed
    {
        $reflect = ReflectionManager::reflectMethod($this->instance, $method);

        return static::invokeMethod([$this->instance, $reflect], $args, $accessible);
    }

    /**
     * @param array $object
     * @param array $args
     * @param bool $accessible
     * @return mixed
     */
    public static function invokeMethod(array $object, array $args = [], bool $accessible = false): mixed
    {
        [$instance, $reflect] = $object;
        if ($accessible) {
            $reflect->setAccessible($accessible);
        }
        return $reflect->invokeArgs($instance, $args);
    }

    /**
     * @param callable|null $resolve
     * @return object
     * @throws ReflectionException
     */
    protected function newInstanceArgs(?callable $resolve = null): object
    {
        $constructor = $this->reflect->getConstructor();
        $args = $constructor && is_callable($resolve) ? $resolve($constructor) : [];
        return $this->reflect->newInstanceArgs($args);
    }

    /**
     * @return object
     */
    public function getInstance(): object
    {
        return $this->instance;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return ReflectionClass
     */
    public function getReflect(): ReflectionClass
    {
        return $this->reflect;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws ReflectionException
     */
    public function __call(string $name, array $args)
    {
        return $this->invoke($name, $args);
    }
}