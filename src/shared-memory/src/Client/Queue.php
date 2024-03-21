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
        switch ($data['type']) {
            case 'consume':
                if (isset($this->callbacks['consume'][$data['queue']])) {
                    call_user_func($this->callbacks['consume'][$data['queue']], $data);
                }
                break;
        }
    }

    /**
     * @param string $queue
     * @param callable $callback
     * @return mixed
     */
    public function addConsumer(string $queue, callable $callback): mixed
    {
        $this->callbacks['consume'][$queue] = $callback;
        return $this->conn->sendCommand('channel:addConsumer', [$queue]);
    }
}