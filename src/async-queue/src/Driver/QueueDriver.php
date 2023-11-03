<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Driver;

use Larmias\AsyncQueue\Contracts\QueueDriverInterface;
use Larmias\Contracts\Concurrent\ConcurrentInterface;
use Larmias\Contracts\Concurrent\ParallelInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Contracts\TimerInterface;
use Throwable;
use function array_merge;
use function Larmias\Support\format_exception;
use function method_exists;

abstract class QueueDriver implements QueueDriverInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var ConcurrentInterface|null
     */
    protected ?ConcurrentInterface $concurrent = null;

    /**
     * @var ParallelInterface|null
     */
    protected ?ParallelInterface $parallel = null;

    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     * @param TimerInterface $timer
     * @param LoggerInterface|null $logger
     * @param array $config
     */
    public function __construct(
        protected ContainerInterface $container,
        protected ContextInterface   $context,
        protected TimerInterface     $timer,
        protected ?LoggerInterface   $logger = null,
        array                        $config = []
    )
    {
        $this->config = array_merge($this->config, $config);
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @return void
     */
    public function consumer(): void
    {
        if ($this->context->inCoroutine()) {
            $this->coHandle();
        } else {
            $this->timer->tick(1, [$this, 'handle']);
        }
    }

    /**
     * @return void
     */
    public function coHandle(): void
    {
        $concurrentLimit = $this->config['concurrent_limit'] ?? null;

        if ($concurrentLimit) {
            $this->concurrent = $this->container->make(ConcurrentInterface::class, ['limit' => (int)$concurrentLimit], true);
        } else {
            $this->parallel = $this->container->make(ParallelInterface::class, [], true);
        }

        while (true) {
            try {
                if ($this->concurrent) {
                    $this->concurrent->create([$this, 'handle']);
                } else {
                    $this->parallel->add([$this, 'handle']);
                    $this->parallel->wait();
                }
            } catch (Throwable $e) {
                $this->logger?->error(format_exception($e));
            }
        }
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $message = $this->pop($this->config['timeout']);
        if (!$message) {
            return;
        }
        try {
            $message->getJob()->handle($message, $this);
        } catch (Throwable $e) {
            $this->logger?->error(format_exception($e));
            $this->fail($message, $message->getAttempts() < $message->getMaxAttempts());
        }
    }
}