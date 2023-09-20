<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\WebSocketServer\Contracts\EventInterface;

class Event implements EventInterface
{
    /**
     * @var callable[]
     */
    protected array $events = [];

    /**
     * 监听事件
     * @param string $name
     * @param callable $callback
     * @return EventInterface
     */
    public function on(string $name, callable $callback): EventInterface
    {
        $this->events[$name] = $callback;
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
        return call_user_func($this->events[$name], ...$args);
    }
}