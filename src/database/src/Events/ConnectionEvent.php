<?php

declare(strict_types=1);

namespace Larmias\Database\Events;

use Larmias\Database\Contracts\ConnectionInterface;

abstract class ConnectionEvent
{
    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(public ConnectionInterface $connection)
    {
    }
}