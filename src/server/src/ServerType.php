<?php

declare(strict_types=1);

namespace Larmias\Server;

class ServerType
{
    /** @var int */
    public const SERVER_TCP = 1;

    /** @var int */
    public const SERVER_UPD = 2;

    /** @var int */
    public const SERVER_HTTP = 3;

    /** @var int */
    public const SERVER_WEBSOCKET = 4;

    /** @var int */
    public const SERVER_PROCESS = 5;

    public static function getSchema(int $type): ?string
    {
        return null;
    }
}