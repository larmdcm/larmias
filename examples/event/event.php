<?php

use Larmias\Event\Contracts\ListenerInterface;

class HelloEvent
{
    public function __construct(public string $message)
    {
    }
}

class HelloListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            HelloEvent::class,
        ];
    }

    public function process(object $event): void
    {
        echo $event->message . PHP_EOL;
    }
}
