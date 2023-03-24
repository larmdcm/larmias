<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Database\Contracts\SqlPrepareInterface;

class SqlPrepare implements SqlPrepareInterface
{
    /**
     * @param string $sql
     * @param array $binds
     */
    public function __construct(protected string $sql = '', protected array $binds = [])
    {
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     */
    public function setSql(string $sql): SqlPrepare
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return array
     */
    public function getBinds(): array
    {
        return $this->binds;
    }

    /**
     * @param array $binds
     */
    public function setBinds(array $binds): SqlPrepare
    {
        $this->binds = $binds;
        return $this;
    }
}