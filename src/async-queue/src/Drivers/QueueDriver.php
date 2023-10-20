<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Drivers;

use Larmias\AsyncQueue\Contracts\QueueDriverInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Contracts\TimerInterface;
use Throwable;
use function array_merge;
use function Larmias\Utils\format_exception;
use function method_exists;

abstract class QueueDriver implements QueueDriverInterface
{
    /**
     * @var array
     */
    protected array $config = [];

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
            return;
        }

        $this->timer->tick(1, [$this, 'processHandle']);
    }

    /**
     * @return void
     */
    public function processHandle(): void
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