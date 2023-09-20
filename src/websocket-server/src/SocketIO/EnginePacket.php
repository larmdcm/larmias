<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\SocketIO;

class EnginePacket
{
    /**
     * Engine.io packet type `open`.
     */
    const OPEN = 0;

    /**
     * Engine.io packet type `close`.
     */
    const CLOSE = 1;

    /**
     * Engine.io packet type `ping`.
     */
    const PING = 2;

    /**
     * Engine.io packet type `pong`.
     */
    const PONG = 3;

    /**
     * Engine.io packet type `message`.
     */
    const MESSAGE = 4;

    /**
     * Engine.io packet type 'upgrade'
     */
    const UPGRADE = 5;

    /**
     * Engine.io packet type `noop`.
     */
    const NOOP = 6;

    /**
     * @var int
     */
    public int $type;

    /**
     * @var string
     */
    public string $data = '';

    public function __construct(int $type, string $data = '')
    {
        $this->type = $type;
        $this->data = $data;
    }

    public static function open(string $payload): static
    {
        return new static(self::OPEN, $payload);
    }

    public static function pong(string $payload = ''): static
    {
        return new static(self::PONG, $payload);
    }

    public static function ping(): static
    {
        return new static(self::PING);
    }

    public static function message(string $payload): static
    {
        return new static(self::MESSAGE, $payload);
    }

    public static function fromString(string $packet): static
    {
        return new static((int)substr($packet, 0, 1), substr($packet, 1) ?? '');
    }

    public function toString(): string
    {
        return (string)$this->type . $this->data;
    }
}