<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Contracts;

interface SidProviderInterface
{
    /**
     * @param int $id
     * @return string
     */
    public function getSid(int $id): string;

    /**
     * @param string $sid
     * @return int
     */
    public function getId(string $sid): int;

    /**
     * @return bool
     */
    public function isLocal(): bool;
}