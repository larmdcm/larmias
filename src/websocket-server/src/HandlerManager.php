<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\WebSocketServer\Contracts\HandlerInterface;

class HandlerManager
{
    /**
     * @var HandlerInterface[]
     */
    protected array $handlers = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param int $id
     * @return HandlerInterface|null
     */
    public function get(int $id): ?HandlerInterface
    {
        return $this->handlers[$id] ?? null;
    }

    /**
     * @param int $id
     * @return HandlerInterface
     */
    public function remember(int $id): HandlerInterface
    {
        if (!isset($this->handlers[$id])) {
            /** @var Socket $socket */
            $socket = $this->container->make(Socket::class, [], true);
            $socket->setId($id);
            $this->handlers[$id] = $this->container->make(HandlerInterface::class, ['socket' => $socket], true);
        }

        return $this->handlers[$id];
    }
}