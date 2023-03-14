<?php

declare(strict_types=1);

namespace Larmias\Snowflake\Contracts;

use Larmias\Contracts\IdGeneratorInterface as BaseIdGeneratorInterface;

interface IdGeneratorInterface extends BaseIdGeneratorInterface
{
    /**
     * @param string $id
     * @param bool $transform
     * @return array
     */
    public function parseId(string $id, bool $transform = false): array;

    /**
     * @return SequenceResolverInterface
     */
    public function getSequenceResolver(): SequenceResolverInterface;

    /**
     * @param SequenceResolverInterface $sequenceResolver
     * @return IdGeneratorInterface
     */
    public function setSequenceResolver(SequenceResolverInterface $sequenceResolver): IdGeneratorInterface;

    /**
     * @return int
     */
    public function getStartTimestamp(): int;

    /**
     * @param int $startTimestamp
     * @return IdGeneratorInterface
     * @throws \Exception
     */
    public function setStartTimestamp(int $startTimestamp): IdGeneratorInterface;

    /**
     * @return int
     */
    public function getWorkerId(): int;

    /**
     * @param int $workerId
     * @return IdGeneratorInterface
     * @throws \Exception
     */
    public function setWorkerId(int $workerId): IdGeneratorInterface;

    /**
     * @return int
     */
    public function getDatacenterId(): int;

    /**
     * @param int $datacenterId
     * @return IdGeneratorInterface
     * @throws \Exception
     */
    public function setDatacenterId(int $datacenterId): IdGeneratorInterface;
}