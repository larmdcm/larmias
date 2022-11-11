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
}