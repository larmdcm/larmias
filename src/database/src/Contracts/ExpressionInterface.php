<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface ExpressionInterface
{
    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param string $value
     */
    public function setValue(string $value): ExpressionInterface;
    /**
     * @return array
     */
    public function getBindings(): array;

    /**
     * @param array $bindings
     */
    public function setBindings(array $bindings): ExpressionInterface;
}