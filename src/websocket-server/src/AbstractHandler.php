<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\WebSocket\FrameInterface;
use Larmias\WebSocketServer\Contracts\EventInterface;
use Larmias\WebSocketServer\CoreMiddleware\WebSocketCoreMiddleware;
use Larmias\Contracts\CoreMiddlewareInterface;
use Closure;

abstract class AbstractHandler
{
    /**
     * @var CoreMiddlewareInterface
     */
    protected CoreMiddlewareInterface $coreMiddleware;

    /**
     * 初始化
     * @param Socket $socket
     * @param ContainerInterface $container
     * @param EventInterface $event
     */
    public function __construct(protected Socket $socket, protected ContainerInterface $container, protected EventInterface $event)
    {
        $this->coreMiddleware = $this->container->make(WebSocketCoreMiddleware::class);

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * 推送数据
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void
    {
        $this->socket->push($data);
    }

    /**
     * 中间件调度
     * @param FrameInterface $frame
     * @param Closure $handler
     * @return mixed
     */
    public function dispatch(FrameInterface $frame, Closure $handler): mixed
    {
        return $this->coreMiddleware->dispatch($frame, function (FrameInterface $frame) use ($handler) {
            return $handler($frame);
        });
    }

    /**
     * 触发事件
     * @param string $event
     * @param ...$args
     * @return mixed
     */
    public function trigger(string $event, ...$args): mixed
    {
        array_unshift($args, $this->socket);
        return $this->event->trigger($event, ...$args);
    }
}