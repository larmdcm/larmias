<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\SidProvider;

use Larmias\WebSocketServer\Contracts\SidProviderInterface;

class LocalSidProvider implements SidProviderInterface
{
    public function getSid(int $id): string
    {
        return (string)$id;
    }

    public function getId(string $sid): int
    {
        return (int)$sid;
    }

    public function isLocal(): bool
    {
        return true;
    }
}