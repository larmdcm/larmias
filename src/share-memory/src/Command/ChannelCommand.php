<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

class ChannelCommand extends Command
{
    public function subscribe(): string
    {
        return 'ok';
    }

    public function publish(): string
    {
        return 'ok';
    }
}