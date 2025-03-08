<?php

declare(strict_types=1);

namespace Larmias\Task\Client;

use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\ContextInterface;

class SyncWait
{
    /**
     * @var ChannelInterface[]
     */
    protected array $channels = [];

    /**
     * @param ContextInterface $context
     * @param ChannelFactoryInterface $factory
     * @param float $timeout
     */
    public function __construct(protected ContextInterface $context, protected ChannelFactoryInterface $factory, protected float $timeout = 10.0)
    {
    }

    /**
     * @param string $id
     * @return void
     */
    public function add(string $id): void
    {
        if ($this->context->inCoroutine() && !$this->context->inFiber()) {
            $this->channels[$id] = $this->factory->make();
        }
    }

    /**
     * @param string $id
     * @param mixed|null $result
     * @return void
     */
    public function done(string $id, mixed $result = null): void
    {
        if (!isset($this->channels[$id])) {
            return;
        }

        $this->channels[$id]->push($result, $this->timeout);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function wait(string $id): mixed
    {
        if (!$this->context->inCoroutine() || $this->context->inFiber()) {
            return true;
        }

        if (!isset($this->channels[$id])) {
            return null;
        }

        $result = $this->channels[$id]->pop($this->timeout);
        $this->channels[$id]->close();
        unset($this->channels[$id]);
        return $result;
    }
}