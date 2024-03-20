<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\SharedMemory\Client\Connection;

/**
 * @mixin Connection
 */
trait Channel
{
    /**
     * @param string|array $channels
     * @param string $message
     * @return bool
     */
    public function publish(string|array $channels, string $message): bool
    {
        return $this->sendCommand('channel:publish', [$channels, $message]);
    }
}