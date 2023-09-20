<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Message;

class Event
{
    public function __construct(public string $type, public mixed $data)
    {
    }
}