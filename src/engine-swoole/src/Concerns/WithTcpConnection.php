<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Concerns;

trait WithTcpConnection
{
    public int $lastHeartbeatTime = 0;

    public bool $processing = false;
}