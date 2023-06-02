<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Drivers;

use Larmias\AsyncQueue\Contracts\QueueDriverInterface;
use Larmias\Contracts\ContainerInterface;
use Throwable;
use function array_merge;
use function method_exists;

abstract class QueueDriver implements QueueDriverInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
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
        $message = $this->pop($this->config['timeout']);
        if (!$message) {
            return;
        }
        try {
            $message->getJob()->handle($message, $this);
        } catch (Throwable $e) {
            $this->fail($message, $message->getAttempts() < $message->getMaxAttempts());
        }
    }
}