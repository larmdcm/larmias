<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Message;

use Larmias\AsyncQueue\Contracts\MessageInterface;
use Serializable;
use function serialize;
use function unserialize;

class Message implements MessageInterface, Serializable
{
    /**
     * @param string $handler
     * @param array $data
     * @param string $messageId
     * @param int $attempts
     * @param int $maxAttempts
     * @param string|null $queue
     */
    public function __construct(
        protected string  $handler,
        protected array   $data = [],
        protected string  $messageId = '',
        protected int     $attempts = 0,
        protected int     $maxAttempts = 0,
        protected ?string $queue = null,
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
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @param string $handler
     * @return MessageInterface
     */
    public function setHandler(string $handler): MessageInterface
    {
        $this->handler = $handler;
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
            'messageId' => $this->messageId,
            'handler' => $this->handler,
            'data' => $this->data,
            'attempts' => $this->attempts,
            'maxAttempts' => $this->maxAttempts,
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
        $this->messageId = $object['messageId'];
        $this->handler = $object['handler'];
        $this->data = $object['data'];
        $this->attempts = $object['attempts'];
        $this->maxAttempts = $object['maxAttempts'];
        $this->queue = $object['queue'];
    }
}