<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueStatusInterface
{
    /**
     * @return int
     */
    public function getWaiting(): int;

    /**
     * @return int
     */
    public function getDelayed(): int;

    /**
     * @return int
     */
    public function getFailed(): int;

    /**
     * @return int
     */
    public function getTimeout(): int;
}