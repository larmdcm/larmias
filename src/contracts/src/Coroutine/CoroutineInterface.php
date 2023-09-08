<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

use ArrayObject;

interface CoroutineInterface
{
    /**
     * 创建协程并执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public function create(callable $callable, ...$params): CoroutineCallableInterface;

    /**
     * 获取协程id
     * @return int
     */
    public function id(): int;

    /**
     * 获取协程pid
     * @param int|null $id
     * @return int
     */
    public function pid(?int $id = null): int;

    /**
     * 设置协程配置
     * @param array $config
     * @return void
     */
    public function set(array $config): void;

    /**
     * 协程结束执行
     * @param callable $callable
     * @return void
     */
    public function defer(callable $callable): void;

    /**
     * 获取协程上下文对象
     * @param int|null $id
     * @return ArrayObject|null
     */
    public function getContextFor(?int $id = null): ?ArrayObject;
}