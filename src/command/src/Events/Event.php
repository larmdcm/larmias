<?php

declare(strict_types=1);

namespace Larmias\Command\Events;

use Larmias\Command\Command;

abstract class Event
{
	public Command $command;

    /**
     * @param Command $command
     */
    public function __construct(Command $command)
    {
    	$this->command = $command;
    }
}