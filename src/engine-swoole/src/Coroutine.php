<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\CoroutineInterface;
use Swoole\Coroutine as SwooleCoroutine;
use ArrayObject;
use RuntimeException;
use function sprintf;
use function max;

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