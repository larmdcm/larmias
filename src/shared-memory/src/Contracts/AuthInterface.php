<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

use Larmias\SharedMemory\Message\Command;

interface AuthInterface
{
    public function login(array $params,bool $throwException = true): bool;

    public function check(Command $command,bool $throwException = true): bool;
}