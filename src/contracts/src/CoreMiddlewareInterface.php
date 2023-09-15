<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Closure;

interface CoreMiddlewareInterface
{
    /**
     * 导入中间件
     * @param array $middleware
     * @return CoreMiddlewareInterface
     */
    public function import(array $middleware): CoreMiddlewareInterface;

    /**
     * 设置中间件
     * @param array $middleware
     * @return CoreMiddlewareInterface
     */
    public function set(array $middleware): CoreMiddlewareInterface;

    /**
     * 添加中间件到尾部
     * @param string|Closure $middleware
     * @return CoreMiddlewareInterface
     */
    public function push(string|Closure $middleware): CoreMiddlewareInterface;

    /**
     * 添加中间件到首部
     * @param string|Closure $middleware
     * @return CoreMiddlewareInterface
     */
    public function unshift(string|Closure $middleware): CoreMiddlewareInterface;

    /**
     * 调度
     * @param mixed $passable
     * @param Closure $handler
     * @return mixed
     */
    public function dispatch(mixed $passable, Closure $handler): mixed;

    /**
     * 返回全部中间件
     * @return array
     */
    public function all(): array;
}