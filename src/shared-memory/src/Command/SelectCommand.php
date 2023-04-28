<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\SharedMemory\Context;

class SelectCommand extends Command
{
    /**
     * @return string
     */
    public function handle(): string
    {
        $select = $this->command->args[0] ?? 'default';
        Context::setStoreSelect((string)$select);
        return 'ok';
    }
}