<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Coroutine;

use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Swoole\Coroutine as SwooleCoroutine;
use RuntimeException;
use function is_int;

class CoroutineCallable implements CoroutineCallableInterface
{
    /**
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * 协程执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public function execute(callable $callable, ...$params): CoroutineCallableInterface
    {
        $id = SwooleCoroutine::create($callable, ...$params);
        $this->id = is_int($id) ? $id : null;
        return $this;
    }

    /**
     * 获取创建的协程id
     * @return int
     */
    public function getId(): int
    {
        if (is_null($this->id)) {
            throw new RuntimeException('Coroutine was not be executed.');
        }

        return $this->id;
    }
}