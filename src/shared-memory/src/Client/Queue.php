<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\SharedMemory\Client\Command\AsyncCommand;

class Queue extends AsyncCommand
{
    /**
     * @param array $data
     * @return void
     */
    protected function onMessage(array $data): void
    {
        if (isset($data['queue']) && isset($this->callbacks[$data['type']][$data['queue']])) {
            call_user_func($this->callbacks[$data['type']][$data['queue']], $data['data']);
        }
    }

    /**
     * @param string $queue
     * @param callable $callback
     * @return bool
     */
    public function addConsumer(string $queue, callable $callback): bool
    {
        $this->callbacks['consume'][$queue] = $callback;
        return $this->conn->sendCommand('queue:addConsumer', [$queue]);
    }

    /**
     * @param string $queue
     * @return bool
     */
    public function delConsumer(string $queue): bool
    {
        return $this->conn->sendCommand('queue:delConsumer', [$queue]);
    }

    /**
     * @param string $queue
     * @return bool
     */
    public function hasConsumer(string $queue): bool
    {
        return $this->conn->sendCommand('queue:hasConsumer', [$queue]);
    }
}