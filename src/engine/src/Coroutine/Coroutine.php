<?php

declare(strict_types=1);

namespace Larmias\Engine\Coroutine;

use ArrayObject;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Engine\Coroutine as Co;

class Coroutine implements CoroutineInterface
{
    public function create(callable $callable, ...$params): CoroutineCallableInterface
    {
        return Co::create(...func_get_args());
    }

    public function id(): int
    {
        return Co::id();
    }

    public function pid(?int $id = null): int
    {
        return Co::pid(...func_get_args());
    }

    public function set(array $config): void
    {
        Co::set(...func_get_args());
    }

    public function defer(callable $callable): void
    {
        Co::defer(...func_get_args());
    }

    public function getContextFor(?int $id = null): ?ArrayObject
    {
        return Co::getContextFor(...func_get_args());
    }

    public function yield(): void
    {
        Co::yield();
    }

    public function resume(int $id): void
    {
        Co::resume($id);
    }
}