<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface BuilderInterface
{
    /**
     * 查询
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function select(array $options): SqlPrepareInterface;

    /**
     * 新增
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function insert(array $options): SqlPrepareInterface;

    /**
     * 批量新增
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function insertAll(array $options): SqlPrepareInterface;

    /**
     * 修改
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function update(array $options): SqlPrepareInterface;

    /**
     * 删除
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function delete(array $options): SqlPrepareInterface;
}