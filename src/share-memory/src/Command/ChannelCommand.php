<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\ShareMemory\StoreManager;

class ChannelCommand extends Command
{
    public function subscribe(): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => StoreManager::channel()->subscribe($this->command->args, $this->getConnection()->getId())
        ];
    }

    public function publish(): string
    {
        return 'ok';
    }
}