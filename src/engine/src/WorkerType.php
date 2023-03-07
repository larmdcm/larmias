<?php

declare(strict_types=1);

namespace Larmias\Engine;

class WorkerType
{
    /** @var int */
    public const TCP_SERVER = 1;

    /** @var int */
    public const UPD_SERVER = 2;

    /** @var int */
    public const HTTP_SERVER = 3;

    /** @var int */
    public const WEBSOCKET_SERVER = 4;

    /** @var int */
    public const WORKER_PROCESS = 5;

    /**
     * @param int $type
     * @return string
     */
    public static function getName(int $type): string
    {
        return match ($type) {
            self::TCP_SERVER => 'Tcp Server',
            self::UPD_SERVER => 'Udp Server',
            self::HTTP_SERVER => 'Http Server',
            self::WEBSOCKET_SERVER => 'WebSocket Server',
            self::WORKER_PROCESS => 'Process',
            default => 'Unknown',
        };
    }
}