<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface BuilderInterface
{
    /**
     * @param QueryInterface $query
     * @return SqlPrepareInterface
     */
    public function select(QueryInterface $query): SqlPrepareInterface;
}