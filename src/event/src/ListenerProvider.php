<?php

declare(strict_types=1);

namespace Larmias\Event;

use Psr\EventDispatcher\ListenerProviderInterface;
use SplPriorityQueue;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var ListenerData[]
     */
    public array $listeners = [];

    /**
     * @param object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $queue = new SplPriorityQueue();
        foreach ($this->listeners as $listener) {
            if ($event instanceof $listener->event) {
                $queue->insert($listener->listener, $listener->priority);
            }
        }
        return $queue;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param int $priority
     */
    public function on(string $event, callable $listener, int $priority = 1): void
    {
        $this->listeners[] = new ListenerData($event, $listener, $priority);
    }
}