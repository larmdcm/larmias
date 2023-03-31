<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface TransactionInterface
{
    /**
     * @return void
     */
    public function commit(): void;

    /**
     * @return void
     */
    public function rollback(): void;
}