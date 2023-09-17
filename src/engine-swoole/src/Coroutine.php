<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use ArrayObject;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Engine\Swoole\Coroutine\CoroutineCallable;
use RuntimeException;
use Swoole\Coroutine as SwooleCoroutine;
use Swoole\Coroutine\Channel;
use function max;
use function sprintf;

class Coroutine implements CoroutineInterface
{
    /**
     * 创建协程并执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public function create(callable $callable, ...$params): CoroutineCallableInterface
    {
        $coCallable = new CoroutineCallable();
        return $coCallable->execute($callable, ...$params);
    }

    /**
     * 创建协程执行并等待协程执行完毕
     * @param callable $callable
     * @param ...$params
     * @return void
     */
    public function runWithBarrier(callable $callable, ...$params): void
    {
        $channel = new Channel(1);

        $this->create(function (...$params) use ($channel, $callable) {
            $this->defer(function () use ($channel) {
                $channel->close();
            });

            call_user_func_array($callable, $params);
        }, ...$params);

        $channel->pop();
    }

    /**
     * 获取协程id
     * @return int
     */
    public function id(): int
    {
        return SwooleCoroutine::getCid();
    }

    /**
     * 获取协程pid
     * @param int|null $id
     * @return int
     */
    public function pid(?int $id = null): int
    {
        if ($id) {
            $cid = SwooleCoroutine::getPcid($id);
            if ($cid === false) {
                throw new RuntimeException(sprintf('Coroutine #%d has been destroyed.', $id));
            }
        } else {
            $cid = SwooleCoroutine::getPcid();
        }
        if ($cid === false) {
            throw new RuntimeException('Non-Coroutine environment don\'t has parent coroutine id.');
        }
        return max(0, $cid);
    }

    /**
     * 设置协程配置
     * @param array $config
     * @return void
     */
    public function set(array $config): void
    {
        SwooleCoroutine::set($config);
    }

    /**
     * 协程结束执行
     * @param callable $callable
     * @return void
     */
    public function defer(callable $callable): void
    {
        SwooleCoroutine::defer($callable);
    }

    /**
     * 获取协程上下文对象
     * @param int|null $id
     * @return ArrayObject|null
     */
    public function getContextFor(?int $id = null): ?ArrayObject
    {
        return $id === null ? SwooleCoroutine::getContext() : SwooleCoroutine::getContext($id);
    }

    /**
     * 让出当前协程的执行权
     * @return void
     */
    public function yield(): void
    {
        SwooleCoroutine::yield();
    }

    /**
     * 恢复协程执行权
     * @param int $id
     * @return void
     */
    public function resume(int $id): void
    {
        SwooleCoroutine::resume($id);
    }
}