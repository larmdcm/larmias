<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\ShareMemory\StoreManager;

class MapCommand extends Command
{
    public function __call(string $name, array $args): mixed
    {
        return \call_user_func_array([StoreManager::map(),$name],$args);
    }
}