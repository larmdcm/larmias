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
    public function getBindings(): array;

    /**
     * @param array $bindings
     */
    public function setBindings(array $bindings): SqlPrepareInterface;
}