<?php

declare(strict_types=1);

namespace Larmias\Utils\Reflection;

use ReflectionClass;
use ReflectionMethod;

class ClassInvoker
{
    /** @var object|null */
    protected ?object $instance = null;

    /** @var ReflectionClass */
    protected ReflectionClass $reflect;

    /** @var string  */
    protected string $className;

    /** @var ParameterBind  */
    protected ParameterBind $parameterBind;

    /**
     * ClassInvoker constructor.
     *
     * @param string|object $object
     * @param array $args
     * @throws \ReflectionException
     */
    public function __construct(string|object $object, array $args = [])
    {
        $this->reflect = new ReflectionClass($object);
        $this->instance = \is_object($object) ? $object : $this->newInstanceArgs($args);
        $this->className = \get_class($this->instance);
        $this->parameterBind = new ParameterBind();
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \ReflectionException
     */
    public function __get(string $name): mixed
    {
        $property = $this->reflect->getProperty($name);

        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }

        return $property->getValue($this->instance);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \ReflectionException
     */
    public function __set(string $name, mixed $value)
    {
        $property = $this->reflect->getProperty($name);

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
     * @throws \ReflectionException
     */
    public function invoke(string $method, array $args = [], bool $accessible = false): mixed
    {
        $reflect = new ReflectionMethod($this->instance, $method);
        $args = $this->parameterBind->setReflect($reflect)->invoke($args);
        if ($accessible) {
            $reflect->setAccessible($accessible);
        }
        return $reflect->invokeArgs($this->instance, $args);
    }

    /**
     * @param array $args
     * @return object
     * @throws \ReflectionException
     */
    protected function newInstanceArgs(array $args = []): object
    {
        $constructor = $this->reflect->getConstructor();

        $args = $constructor ? $this->parameterBind->setReflect($constructor)->invoke($args) : [];

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
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call(string $name,array $args)
    {
        return $this->invoke($name,$args);
    }
}