<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Message;

use Larmias\AsyncQueue\Contracts\JobInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\Stringable\Str;
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
        protected array        $data = [],
        protected string       $messageId = '',
        protected int          $attempts = 0,
        protected int          $maxAttempts = 0,
        protected ?string      $queue = null,
    )
    {
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param string $messageId
     * @return MessageInterface
     */
    public function setMessageId(string $messageId): MessageInterface
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return JobInterface
     */
    public function getJob(): JobInterface
    {
        return $this->job;
    }

    /**
     * @param JobInterface $job
     * @return MessageInterface
     */
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

    /**
     * @return string|null
     */
    public function getQueue(): ?string
    {
        return $this->queue;
    }

    /**
     * @param string|null $queue
     * @return MessageInterface
     */
    public function setQueue(?string $queue): MessageInterface
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     * @return MessageInterface
     */
    public function setAttempts(int $attempts): MessageInterface
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @param int $maxAttempts
     * @return MessageInterface
     */
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
            'queue' => $this->queue,
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
        $this->queue = $object['queue'];
    }
}