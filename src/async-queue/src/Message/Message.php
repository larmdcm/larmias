<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Message;

use Larmias\AsyncQueue\Contracts\JobInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Serializable;
use function serialize;
use function unserialize;

class Message implements MessageInterface, Serializable
{
    /**
     * @param JobInterface $job
     * @param array $data
     * @param string $messageId
     * @param int $attempts
     * @param int $maxAttempts
     */
    public function __construct(
        protected JobInterface $job, 
        protected array $data = [], 
        protected string $messageId = '', 
        protected int $attempts = 0,
        protected int $maxAttempts = 0)
    {
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): MessageInterface
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getJob(): JobInterface
    {
        return $this->job;
    }

    public function setJob(JobInterface $job): MessageInterface
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return MessageInterface
     */
    public function setData(array $data): MessageInterface
    {
        $this->data = $data;
        return $this;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): MessageInterface
    {
        $this->attempts = $attempts;
        return $this;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setMaxAttempts(int $maxAttempts): MessageInterface
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            'message_id' => $this->messageId,
            'job' => $this->job,
            'data' => $this->data,
            'attempts' => $this->attempts,
            'max_attempts' => $this->maxAttempts,
        ]);
    }

    /**
     * @param string $data
     * @return void
     */
    public function unserialize(string $data): void
    {
        $object = unserialize($data);
        $this->messageId = $object['message_id'];
        $this->job = $object['job'];
        $this->data = $object['data'];
        $this->attempts = $object['attempts'];
        $this->maxAttempts = $object['max_attempts'];
    }
}