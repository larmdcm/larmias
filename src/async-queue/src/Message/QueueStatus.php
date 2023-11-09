<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Message;

use Larmias\AsyncQueue\Contracts\QueueStatusInterface;

class QueueStatus implements QueueStatusInterface
{
    /**
     * @var int
     */
    public int $waiting = 0;

    /**
     * @var int
     */
    public int $delayed = 0;

    /**
     * @var int
     */
    public int $failed = 0;

    /**
     * @var int
     */
    public int $timeout = 0;

    public static function formArray(array $data): QueueStatusInterface
    {
        $object = new static();
        foreach ($data as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }

    /**
     * @return int
     */
    public function getWaiting(): int
    {
        return $this->waiting;
    }

    /**
     * @param int $waiting
     */
    public function setWaiting(int $waiting): void
    {
        $this->waiting = $waiting;
    }

    /**
     * @return int
     */
    public function getDelayed(): int
    {
        return $this->delayed;
    }

    /**
     * @param int $delayed
     */
    public function setDelayed(int $delayed): void
    {
        $this->delayed = $delayed;
    }

    /**
     * @return int
     */
    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     * @param int $failed
     */
    public function setFailed(int $failed): void
    {
        $this->failed = $failed;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }
}