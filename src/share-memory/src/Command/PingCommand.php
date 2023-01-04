<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

class PingCommand extends Command
{
    public function handle(): string
    {
        return 'PONG';
    }
}