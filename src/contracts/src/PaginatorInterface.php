<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface PaginatorInterface
{
    /**
     * 渲染分页
     * @return string
     */
    public function render(): string;

    /**
     * 获取分页总条数
     * @return int
     */
    public function total(): int;

    /**
     * 获取每页数量
     * @return int
     */
    public function listRows(): int;

    /**
     * 获取最后一页页码
     * @return int
     */
    public function lastPage(): int;

    /**
     * 数据是否足够分页
     * @return bool
     */
    public function hasPages(): bool;

    /**
     * @return array
     */
    public function items(): array;

    /**
     * 获取数据集
     *
     * @return CollectionInterface
     */
    public function getCollection(): CollectionInterface;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * 统计数据集条数
     * @return int
     */
    public function count(): int;

    /**
     * 给每个元素执行个回调
     *
     * @param callable $callback
     * @return PaginatorInterface
     */
    public function each(callable $callback): PaginatorInterface;

    /**
     * @return array
     */
    public function toArray(): array;
}