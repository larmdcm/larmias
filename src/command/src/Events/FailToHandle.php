<?php

declare(strict_types=1);

namespace Larmias\Command\Events;

use Larmias\Command\Command;
use Throwable;

class FailToHandle extends Event
{
    public function __construct(Command $command, public Throwable $throwable)
    {
        $this->command = $command;
    }
}