<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface BuilderInterface
{
    /**
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function select(array $options): SqlPrepareInterface;

    /**
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function insert(array $options): SqlPrepareInterface;

    /**
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function insertAll(array $options): SqlPrepareInterface;

    /**
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function update(array $options): SqlPrepareInterface;

    /**
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function delete(array $options): SqlPrepareInterface;
}