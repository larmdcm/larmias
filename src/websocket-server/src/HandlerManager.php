<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\WebSocketServer\Contracts\HandlerInterface;
use Larmias\WebSocketServer\Contracts\SidProviderInterface;

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
            /** @var SidProviderInterface $sidProvider */
            $sidProvider = $this->container->make(SidProviderInterface::class, [], true);
            /** @var Socket $socket */
            $socket = $this->container->make(Socket::class, ['sidProvider' => $sidProvider], true);
            $socket->setId($id);
            $this->handlers[$id] = $this->container->make(HandlerInterface::class, ['socket' => $socket], true);
        }

        return $this->handlers[$id];
    }
}