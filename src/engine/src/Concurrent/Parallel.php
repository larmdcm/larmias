<?php

declare(strict_types=1);

namespace Larmias\Engine\Concurrent;

use Larmias\Contracts\Concurrent\ParallelExecutionException;
use Larmias\Contracts\Concurrent\ParallelInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Contracts\Sync\WaitGroupInterface;
use Throwable;
use function sprintf;

class Parallel implements ParallelInterface
{
    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    /**
     * @var ChannelInterface|null
     */
    protected ?ChannelInterface $concurrentChannel = null;

    /**
     * @var CoroutineInterface
     */
    protected CoroutineInterface $coroutine;

    /**
     * @var array
     */
    protected array $results = [];

    /**
     * @var Throwable[]
     */
    protected array $throwable = [];


    /**
     * @param ContainerInterface $container
     * @param int $concurrent
     * @throws Throwable
     */
    public function __construct(protected ContainerInterface $container, int $concurrent = 0)
    {
        $this->coroutine = $this->container->get(CoroutineInterface::class);

        if ($concurrent > 0) {
            /** @var ChannelInterface $channel */
            $this->concurrentChannel = $this->container->get(ChannelFactoryInterface::class)->create($concurrent);
        }
    }

    public function add(callable $callable, $key = null): void
    {
        if (is_null($key)) {
            $this->callbacks[] = $callable;
        } else {
            $this->callbacks[$key] = $callable;
        }
    }

    public function wait(bool $throw = true): array
    {
        $wg = $this->container->make(WaitGroupInterface::class, [], true);
        $wg->add(count($this->callbacks));
        foreach ($this->callbacks as $key => $callback) {
            $this->concurrentChannel && $this->concurrentChannel->push(true);
            $this->results[$key] = null;
            $this->coroutine->create(function () use ($callback, $key, $wg) {
                try {
                    $this->results[$key] = $callback();
                } catch (Throwable $throwable) {
                    $this->throwable[$key] = $throwable;
                    unset($this->results[$key]);
                } finally {
                    $this->concurrentChannel && $this->concurrentChannel->pop();
                    $wg->done();
                }
            });
        }
        $wg->wait();
        if ($throw && ($throwableCount = count($this->throwable)) > 0) {
            $message = 'Detecting ' . $throwableCount . ' throwable occurred during parallel execution:' . PHP_EOL . $this->formatThrowable($this->throwable);
            $executionException = new ParallelExecutionException($message);
            $executionException->setResults($this->results);
            $executionException->setThrowables($this->throwable);
            unset($this->results, $this->throwable);
            throw $executionException;
        }
        return $this->results;
    }

    public function count(): int
    {
        return count($this->callbacks);
    }

    public function clear(): void
    {
        $this->callbacks = [];
        $this->results = [];
        $this->throwable = [];
    }

    /**
     * Format throwable into a nice list.
     *
     * @param Throwable[] $throwable
     */
    protected function formatThrowable(array $throwable): string
    {
        $output = '';
        foreach ($throwable as $key => $value) {
            $output .= sprintf('(%s) %s: %s' . PHP_EOL . '%s' . PHP_EOL, $key, get_class($value), $value->getMessage(), $value->getTraceAsString());
        }
        return $output;
    }
}