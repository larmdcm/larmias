<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Providers;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\WebSocketServer\Annotation\Handler\EventHandler;
use Larmias\WebSocketServer\ConnectionManager;
use Larmias\WebSocketServer\Contracts\ConnectionManagerInterface;
use Larmias\WebSocketServer\Contracts\EventInterface;
use Larmias\WebSocketServer\Contracts\HandlerInterface;
use Larmias\WebSocketServer\Contracts\PusherInterface;
use Larmias\WebSocketServer\Contracts\RoomInterface;
use Larmias\WebSocketServer\Contracts\SidProviderInterface;
use Larmias\WebSocketServer\Room\Memory;
use Larmias\WebSocketServer\SidProvider\LocalSidProvider;
use Larmias\WebSocketServer\SocketIO\Handler;
use Larmias\WebSocketServer\SocketIO\Pusher;
use Larmias\WebSocketServer\Event;

class WebSocketServerServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        $this->container->bindIf([
            ConnectionManagerInterface::class => ConnectionManager::class,
            RoomInterface::class => Memory::class,
            PusherInterface::class => Pusher::class,
            HandlerInterface::class => Handler::class,
            EventInterface::class => Event::class,
            SidProviderInterface::class => LocalSidProvider::class,
        ]);

        if ($this->container->has(AnnotationInterface::class)) {
            /** @var AnnotationInterface $annotation */
            $annotation = $this->container->get(AnnotationInterface::class);
            $annotation->addHandler([
                Event::class,
            ], EventHandler::class);
        }
    }

    public function boot(): void
    {
    }
}