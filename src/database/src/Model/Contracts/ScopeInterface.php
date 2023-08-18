<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Contracts;

interface ScopeInterface
{
    /**
     * 将作用域应用于给定的模型查询器
     * @param QueryInterface $query
     * @param ...$args
     * @return void
     */
    public function apply(QueryInterface $query, ...$args): void;
}