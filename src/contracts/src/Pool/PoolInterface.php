<?php

declare(strict_types=1);

namespace Larmias\Pool\Contracts;

interface PoolInterface
{
    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface;
}