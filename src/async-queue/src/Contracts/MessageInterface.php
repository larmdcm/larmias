<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getMessageId(): string;

    /**
     * @param string $messageId
     * @return MessageInterface
     */
    public function setMessageId(string $messageId): MessageInterface;

    /**
     * @return string|null
     */
    public function getQueue(): ?string;

    /**
     * @param string|null $queue
     * @return MessageInterface
     */
    public function setQueue(?string $queue): MessageInterface;

    /**
     * @return JobInterface
     */
    public function getJob(): JobInterface;

    /**
     * @param JobInterface $job
     * @return MessageInterface
     */
    public function setJob(JobInterface $job): MessageInterface;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array $data
     * @return MessageInterface
     */
    public function setData(array $data): MessageInterface;

    /**
     * @return int
     */
    public function getAttempts(): int;

    /**
     * @param int $attempts
     * @return MessageInterface
     */
    public function setAttempts(int $attempts): MessageInterface;

    /**
     * @return int
     */
    public function getMaxAttempts(): int;

    /**
     * @param int $maxAttempts
     * @return MessageInterface
     */
    public function setMaxAttempts(int $maxAttempts): MessageInterface;
}