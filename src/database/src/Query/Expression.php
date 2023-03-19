<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Database\Contracts\ExpressionInterface;

class Expression implements ExpressionInterface
{
    /**
     * @param string $value
     * @param array $binds
     */
    public function __construct(protected string $value, protected array $binds = [])
    {
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): ExpressionInterface
    {
        $this->value = $value;
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
    public function setBinds(array $binds): ExpressionInterface
    {
        $this->binds = $binds;
        return $this;
    }
}