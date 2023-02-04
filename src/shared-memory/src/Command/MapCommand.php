<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\SharedMemory\StoreManager;

class MapCommand extends Command
{
    public function __call(string $name, array $args): mixed
    {
        return \call_user_func_array([StoreManager::map(),$name],$args);
    }
}