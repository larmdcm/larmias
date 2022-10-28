<?php

class EventBase
{
    public function loop()
    {
    }

    public function exit()
    {
    }
}

class Event
{
    const READ = 1;

    const PERSIST = 2;

    const WRITE = 3;

    const TIMEOUT = 4;

    const SIGNAL = 5;

    public function __construct(EventBase $base,$fd,int $flag,callable $callback,array $args = [])
    {
    }

    public function add(): mixed
    {
    }

    public function addTimer($timeout): mixed
    {
    }
}