<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Closure;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * 创建类实例
     *
     * @param string $abstract
     * @param array $params
     * @param boolean $newInstance
     * @return mixed
     */
    public function make(string $abstract, array $params = [], bool $newInstance = false): object;

    /**
     * 绑定类、闭包、实例、接口实现到容器
     *
     * @param string|array $abstract
     * @param mixed|null $concrete
     * @param bool $force
     * @return ContainerInterface
     */
    public function bind(string|array $abstract, mixed $concrete = null, bool $force = true): ContainerInterface;

    /**
     * 不存在则绑定类、闭包、实例、接口实现到容器
     *
     * @param string|array $abstract
     * @param mixed|null $concrete
     * @return ContainerInterface
     */
    public function bindIf(string|array $abstract, mixed $concrete = null): ContainerInterface;

    /**
     * 绑定一个类实例到容器
     *
     * @param string $abstract
     * @param object $instance
     * @param bool $force
     * @return object
     */
    public function instance(string $abstract, object $instance, bool $force = true): object;

    /**
     * 获取类别名的真实类名
     *
     * @param string $abstract
     * @return string
     */
    public function getAlias(string $abstract): string;

    /**
     * 解绑容器中的对象实例
     *
     * @param string $name
     * @return bool
     */
    public function unbind(string $name): bool;

    /**
     * 判断容器中是否存在对象实例
     *
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function exists(string $abstract): bool;

    /**
     * 调用反射执行callable 支持参数绑定
     *
     * @param mixed $callable
     * @param array $params
     * @param bool $accessible
     * @return mixed
     */
    public function invoke(mixed $callable, array $params = [], bool $accessible = false): mixed;

    /**
     * 注册一个容器对象回调
     *
     * @param string|Closure $abstract
     * @param Closure|null $callback
     * @return void
     */
    public function resolving(string|Closure $abstract, Closure $callback = null): void;
}