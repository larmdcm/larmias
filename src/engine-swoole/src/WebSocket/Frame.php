<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\WebSocket;

use Larmias\Contracts\WebSocket\FrameInterface;
use Swoole\WebSocket\Frame as SwooleFrame;

class Frame implements FrameInterface
{
    public function __construct(protected int $fd, protected mixed $data, protected int $opcode, protected bool $finish)
    {
    }

    public static function from(SwooleFrame $frame): static
    {
        return new static($frame->fd, $frame->data, $frame->opcode, $frame->finish);
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