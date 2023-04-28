<?php

declare(strict_types=1);

namespace Larmias\Contracts\Redis;

interface ConnectionInterface
{
    /**
     * @return bool
     */
    public function connect(): bool;

    /**
     * @return bool
     */
    public function reconnect(): bool;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @return mixed
     */
    public function getRaw(): mixed;

    /**
     * @return int
     */
    public function getDatabase(): int;
    
    /**
     * @param int $db
     * @return void
     */
    public function setDatabase(int $db): void;
}