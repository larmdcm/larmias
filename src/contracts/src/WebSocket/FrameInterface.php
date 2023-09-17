<?php

declare(strict_types=1);

namespace Larmias\Contracts\WebSocket;

interface FrameInterface
{
    /**
     * @return int
     */
    public function getFd(): int;

    /**
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * @return int|null
     */
    public function getOpcode(): ?int;

    /**
     * @return bool
     */
    public function isFinish(): bool;
}