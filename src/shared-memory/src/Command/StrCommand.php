<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\SharedMemory\StoreManager;
use function call_user_func_array;

class StrCommand extends Command
{
    /**
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $name, array $args): mixed
    {
        return call_user_func_array([StoreManager::str(), $name], $args);
    }
}