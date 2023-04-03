<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface ManagerInterface
{
    /**
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface;

    /**
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function query(ConnectionInterface $connection): QueryInterface;
}