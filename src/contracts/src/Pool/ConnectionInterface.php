<?php

declare(strict_types=1);

namespace Larmias\Pool\Contracts;

interface ConnectionInterface
{
    /**
     * @param int $time
     * @return void
     */
    public function setLastActiveTime(int $time): void;

    /**
     * @return int
     */
    public function getLastActiveTime(): int;
}