<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\WebSocket;

use Larmias\Contracts\WebSocket\FrameInterface;

class Frame implements FrameInterface
{
    public function __construct(protected int $fd, protected mixed $data, protected int $opcode, protected bool $finish)
    {
    }

    public static function from(int $fd, mixed $data): static
    {
        return new static($fd, $data, 0x1, true);
    }

    public function getFd(): int
    {
        return $this->fd;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getOpcode(): int
    {
        return $this->opcode;
    }

    public function isFinish(): bool
    {
        return $this->finish;
    }
}