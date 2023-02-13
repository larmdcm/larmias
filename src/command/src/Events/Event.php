<?php

declare(strict_types=1);

namespace Larmias\Command\Events;

use Larmias\Command\Command;

abstract class Event
{
    /**
     * @param Command $command
     */
    public function __construct(public Command $command)
    {
    }
}