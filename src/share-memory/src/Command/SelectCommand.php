<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\ShareMemory\Context;

class SelectCommand extends Command
{
    public function handle(): string
    {
        $select = $this->command->args[0] ?? 'default';
        Context::setStoreSelect((string)$select);
        return 'ok';
    }
}