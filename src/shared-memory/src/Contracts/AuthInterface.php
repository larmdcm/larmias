<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

use Larmias\SharedMemory\Message\Command;

interface AuthInterface
{
    /**
     * @param array $params
     * @param bool $throwException
     * @return bool
     */
    public function login(array $params, bool $throwException = true): bool;

    /**
     * @param Command $command
     * @param bool $throwException
     * @return bool
     */
    public function check(Command $command, bool $throwException = true): bool;
}