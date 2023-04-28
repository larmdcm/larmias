<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

class PingCommand extends Command
{
    /**
     * @return string
     */
    public function handle(): string
    {
        return 'PONG';
    }
}