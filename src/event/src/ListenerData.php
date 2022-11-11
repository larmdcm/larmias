<?php

declare(strict_types=1);

namespace Larmias\Event;

class ListenerData
{
    /**
     * @var callable
     */
    public $listener;

    /**
     * ListenerData constructor.
     *
     * @param string $event
     * @param callable $listener
     * @param int $priority
     */
    public function __construct(public string $event, callable $listener, public int $priority)
    {
        $this->listener = $listener;
    }
}