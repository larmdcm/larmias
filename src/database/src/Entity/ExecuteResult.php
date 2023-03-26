<?php

namespace Larmias\Database\Entity;

use Larmias\Database\Contracts\ExecuteResultInterface;

class ExecuteResult implements ExecuteResultInterface
{
    /**
     * @param string $executeSql
     * @param float $executeTime
     * @param int $rowCount
     * @param array $resultSet
     * @param string|null $insertId
     */
    public function __construct(
        protected string $executeSql = '', protected float $executeTime = 0.0, protected int $rowCount = 0,
        protected array  $resultSet = [], protected ?string $insertId = null,
    )
    {

    }

    /**
     * @return string
     */
    public function getExecuteSql(): string
    {
        return $this->executeSql;
    }

    /**
     * @return float
     */
    public function getExecuteTime(): float
    {
        return $this->executeTime;
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * @return array
     */
    public function getResultSet(): array
    {
        return $this->resultSet;
    }

    /**
     * @return string|null
     */
    public function getInsertId(): ?string
    {
        return $this->insertId;
    }
}