<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use ArrayObject;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Engine\Contracts\CoroutineInterface;
use Larmias\Engine\Swoole\Coroutine\CoroutineCallable;
use RuntimeException;
use Swoole\Coroutine as SwooleCoroutine;
use function max;
use function sprintf;

class Coroutine implements CoroutineInterface
{
    /**
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public static function create(callable $callable, ...$params): CoroutineCallableInterface
    {
        $coCallable = new CoroutineCallable();
        return $coCallable->execute($callable, ...$params);
    }

    /**
     * @return int
     */
    public static function id(): int
    {
        return SwooleCoroutine::getCid();
    }

    /**
     * @param int|null $id
     * @return int
     */
    public static function pid(?int $id = null): int
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
     * @param array $config
     * @return void
     */
    public static function set(array $config): void
    {
        SwooleCoroutine::set($config);
    }

    /**
     * @param callable $callable
     * @return void
     */
    public static function defer(callable $callable): void
    {
        SwooleCoroutine::defer($callable);
    }

    /**
     * @param int|null $id
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject
    {
        return $id === null ? SwooleCoroutine::getContext() : SwooleCoroutine::getContext($id);
    }
}