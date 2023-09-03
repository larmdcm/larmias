<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface CoroutineCallableInterface
{
    /**
     * 协程执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public function execute(callable $callable, ...$params): CoroutineCallableInterface;

    /**
     * 获取创建的协程id
     * @return int
     */
    public function getId(): int;
}