<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Contracts;

interface EventInterface
{
    /**
     * @var string
     */
    public const ON_CONNECT = 'connect';

    /**
     * @var string
     */
    public const ON_DISCONNECT = 'disconnect';

    /**
     * @var string
     */
    public const ON_CLOSE = 'close';

    /**
     * 监听事件
     * @param string $name
     * @param mixed $handler
     * @return EventInterface
     */
    public function on(string $name, mixed $handler): EventInterface;

    /**
     * 是否监听事件
     * @param string $name
     * @return boolean
     */
    public function hasListen(string $name): bool;

    /**
     * 事件触发
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     */
    public function trigger(string $name, ...$args): mixed;
}