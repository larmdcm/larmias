<?php

declare(strict_types=1);

namespace Larmias\Database\Entity;

use Larmias\Database\Contracts\ExpressionInterface;

class Expression implements ExpressionInterface
{
    /**
     * @param string $value
     * @param array $bindings
     */
    public function __construct(protected string $value, protected array $bindings = [])
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
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param array $bindings
     */
    public function setBindings(array $bindings): ExpressionInterface
    {
        $this->bindings = $bindings;
        return $this;
    }
}