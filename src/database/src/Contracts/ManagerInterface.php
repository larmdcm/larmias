<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use Psr\EventDispatcher\EventDispatcherInterface;

interface ManagerInterface
{
    /**
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface;

    /**
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function newQuery(ConnectionInterface $connection): QueryInterface;

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}