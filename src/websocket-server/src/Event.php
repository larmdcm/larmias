<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\WebSocketServer\Contracts\EventInterface;

class Event implements EventInterface
{
    /**
     * @var callable[]
     */
    protected array $events = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * 监听事件
     * @param string $name
     * @param mixed $handler
     * @return EventInterface
     */
    public function on(string $name, mixed $handler): EventInterface
    {
        $this->events[$name] = $handler;
        return $this;
    }

    /**
     * 是否监听事件
     * @param string $name
     * @return boolean
     */
    public function hasListen(string $name): bool
    {
        return isset($this->events[$name]);
    }

    /**
     * 事件触发
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     */
    public function trigger(string $name, ...$args): mixed
    {
        if (!$this->hasListen($name)) {
            return false;
        }

        return $this->container->invoke($this->events[$name], $args);
    }
}