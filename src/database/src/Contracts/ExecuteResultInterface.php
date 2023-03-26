<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface ExecuteResultInterface
{
    /**
     * @return string
     */
    public function getExecuteSql(): string;

    /**
     * @return float
     */
    public function getExecuteTime(): float;

    /**
     * @return int
     */
    public function getRowCount(): int;

    /**
     * @return array
     */
    public function getResultSet(): array;

    /**
     * @return string|null
     */
    public function getInsertId(): ?string;
}