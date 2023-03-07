<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\CoroutineInterface;
use Swoole\Coroutine as SwooleCoroutine;
use ArrayObject;
use RuntimeException;

class Coroutine implements CoroutineInterface
{
    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param callable $callable
     * @param ...$params
     * @return CoroutineInterface
     */
    public static function create(callable $callable, ...$params): CoroutineInterface
    {
        $coroutine = new static($callable);
        $coroutine->execute(...$params);
        return $coroutine;
    }

    /**
     * @param ...$params
     * @return CoroutineInterface
     */
    public function execute(...$params): CoroutineInterface
    {
        $this->id = SwooleCoroutine::create($this->callable, ...$params);
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        if (is_null($this->id)) {
            throw new RuntimeException('Coroutine was not be executed.');
        }
        return $this->id;
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
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject
    {
        return $id === null ? SwooleCoroutine::getContext() : SwooleCoroutine::getContext($id);
    }
}