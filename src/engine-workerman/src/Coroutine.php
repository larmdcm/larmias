<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Workerman\Coroutine as WorkermanCoroutine;
use WeakReference;
use Closure;

class Coroutine implements CoroutineInterface
{
    /** @var array<WeakReference> */
    protected array $map = [];

    public function create(callable $callable, ...$params): CoroutineCallableInterface
    {
        $coCallable = new class() implements CoroutineCallableInterface {
            protected ?Closure $end = null;

            protected WorkermanCoroutine\Coroutine\CoroutineInterface $co;

            public function __construct(protected int $id = 0)
            {
            }

            public function execute(callable $callable, ...$params): CoroutineCallableInterface
            {
                $this->co = WorkermanCoroutine::create(function () use ($callable, $params) {
                    $callable(...$params);
                    $end = $this->end;
                    if ($end) {
                        $end($this);
                    }
                });
                $this->id = $this->co->id();
                return $this;
            }

            public function resume(...$params): void
            {
                $this->co->resume(...$params);
            }

            public function end(Closure $end): CoroutineCallableInterface
            {
                $this->end = $end;

                return $this;
            }

            public function getId(): int
            {
                return $this->id;
            }
        };

        $coCallable = $coCallable->execute($callable, ...$params)->end(function (CoroutineCallableInterface $coCallable) {
            unset($this->map[$coCallable->getId()]);
        });

        $this->map[$coCallable->getId()] = WeakReference::create($coCallable);

        return $coCallable;
    }

    public function id(): int
    {
        return WorkermanCoroutine::getCurrent()->id();
    }

    public function pid(?int $id = null): int
    {
        return 0;
    }

    public function set(array $config): void
    {
    }

    public function defer(callable $callable): void
    {
        WorkermanCoroutine::defer($callable);
    }

    public function yield(mixed $value = null): void
    {
        WorkermanCoroutine::suspend($value);
    }

    public function resume(int $id, mixed ...$params): void
    {
        if (!isset($this->map[$id])) {
            return;
        }
        $co = $this->map[$id]->get();
        unset($this->map[$id]);
        $co?->resume(...$params);
    }
}