<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\SocketIO;

use Larmias\Contracts\WebSocket\FrameInterface;

class Frame implements FrameInterface
{
    public function __construct(protected int $fd, protected mixed $data, protected int $opcode, protected bool $finish)
    {
    }

    public static function from(FrameInterface $frame, ?array $data = null): static
    {
        if ($data === null) {
            $data = $frame->getData();
        }

        return new static($frame->getFd(), $data, $frame->getOpcode(), $frame->isFinish());
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