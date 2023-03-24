<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface SqlPrepareInterface
{
    /**
     * @return string
     */
    public function getSql(): string;

    /**
     * @param string $sql
     */
    public function setSql(string $sql): SqlPrepareInterface;

    /**
     * @return array
     */
    public function getBinds(): array;

    /**
     * @param array $binds
     */
    public function setBinds(array $binds): SqlPrepareInterface;
}