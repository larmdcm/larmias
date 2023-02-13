<?php

namespace Larmias\Command\Events;

use Larmias\Command\Command;

class FailToHandle extends Event
{
    public function __construct(Command $command, public \Throwable $throwable)
    {
        $this->command = $command;
    }
}