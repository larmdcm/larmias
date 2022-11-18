<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface MiddlewareInterface
{
    /**
     * 导入中间件
     *
     * @param array $middlewares
     * @return MiddlewareInterface
     */
    public function import(array $middlewares): MiddlewareInterface;

    /**
     * 添加中间件到尾部
     *
     * @param string|\Closure $middleware
     * @return MiddlewareInterface
     */
    public function push(string|\Closure $middleware): MiddlewareInterface;

    /**
     * 添加中间件到首部
     *
     * @param string|\Closure $middleware
     * @return MiddlewareInterface
     */
    public function unshift(string|\Closure $middleware): MiddlewareInterface;

    /**
     * 管道调度.
     *
     * @return PipelineInterface
     */
    public function pipeline(): PipelineInterface;

    /**
     * @return array
     */
    public function all(): array;

}