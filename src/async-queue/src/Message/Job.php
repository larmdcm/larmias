<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Message;

use Larmias\AsyncQueue\Contracts\JobInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Contracts\QueueDriverInterface;

class Job implements JobInterface
{
    /**
     * @param MessageInterface $message
     * @param QueueDriverInterface $queueDriver
     */
    public function __construct(protected MessageInterface $message, protected QueueDriverInterface $queueDriver)
    {
    }

    /**
     * @return bool
     */
    public function ack(): bool
    {
        return $this->queueDriver->ack($this->message);
    }

    /**
     * @param int $delay
     * @return MessageInterface
     */
    public function reload(int $delay = 0): MessageInterface
    {
        return $this->queueDriver->reload($this->message, $delay);
    }

    /**
     * @return bool
     */
    public function fail(): bool
    {
        return $this->queueDriver->fail($this->message);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return $this->queueDriver->delete($this->message);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->message->getData();
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->message->getAttempts();
    }

    /**
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return $this->message->getMaxAttempts();
    }

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        return $this->message;
    }
}