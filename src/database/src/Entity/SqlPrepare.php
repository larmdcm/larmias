<?php

declare(strict_types=1);

namespace Larmias\Database\Entity;

use Larmias\Database\Contracts\SqlPrepareInterface;

class SqlPrepare implements SqlPrepareInterface
{
    /**
     * @param string $sql
     * @param array $bindings
     */
    public function __construct(protected string $sql = '', protected array $bindings = [])
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
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param array $bindings
     */
    public function setBindings(array $bindings): SqlPrepare
    {
        $this->bindings = $bindings;
        return $this;
    }
}