<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Contracts\ServerInterface;
use Psr\Container\ContainerInterface;

class Worker implements ServerInterface
{
    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     * @param EngineConfig $engineConfig
     * @param WorkerConfig $workerConfig
     */
    public function __construct(protected ContainerInterface $container,protected EngineConfig $engineConfig,protected WorkerConfig $workerConfig)
    {
        $this->initialize();
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->registerEventCallback();
    }

    /**
     *  触发回调函数
     *
     * @param string $event
     * @param array $args
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function trigger(string $event,array $args = []): void
    {
        $callable = $this->callbacks[$event] ?? null;
        if (!$callable) {
            return;
        }
        if (\is_callable($callable)) {
            $callable(...$args);
            return;
        }
        if (!\is_array($callable) && !isset($callable[0]) || !isset($callable[1])) {
            return;
        }
        $object = $this->container->get($callable[0]);
        \call_user_func_array([$object,$callable[1]],$args);
    }

    /**
     * @return void
     */
    private function registerEventCallback(): void
    {
        $this->callbacks = \array_merge($this->engineConfig->getCallbacks(),$this->workerConfig->getCallbacks());
    }
}