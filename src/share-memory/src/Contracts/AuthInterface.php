<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Contracts;

use Larmias\ShareMemory\Message\Command;

interface AuthInterface
{
    public function login(array $params,bool $throwException = true): bool;

    public function check(Command $command,bool $throwException = true): bool;
}