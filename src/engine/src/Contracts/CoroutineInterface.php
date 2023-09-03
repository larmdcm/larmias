<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

use ArrayObject;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;

interface CoroutineInterface
{
    /**
     * 创建协程并执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public static function create(callable $callable, ...$params): CoroutineCallableInterface;

    /**
     * 获取协程id
     * @return int
     */
    public static function id(): int;

    /**
     * 获取协程pid
     * @param int|null $id
     * @return int
     */
    public static function pid(?int $id = null): int;

    /**
     * 设置协程配置
     * @param array $config
     * @return void
     */
    public static function set(array $config): void;

    /**
     * 协程结束执行
     * @param callable $callable
     * @return void
     */
    public static function defer(callable $callable): void;

    /**
     * 获取协程上下文
     * @param int|null $id
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject;
}