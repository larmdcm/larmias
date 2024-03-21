<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\SharedMemory\Client\Command\AsyncCommand;

class Channel extends AsyncCommand
{
    /**
     * @param array $data
     * @return void
     */
    protected function onMessage(array $data): void
    {
        switch ($data['type']) {
            case 'message':
                if (isset($this->callbacks['message'][$data['channel']])) {
                    call_user_func($this->callbacks['message'][$data['channel']], $data);
                }
                break;
            case 'channels':
                if (isset($this->callbacks['channels'])) {
                    foreach ($this->callbacks['channels'] as $callback) {
                        call_user_func($callback, $data);
                    }
                }
                break;
        }
    }

    /**
     * @param string|array $channels
     * @param callable $callback
     * @return bool
     */
    public function subscribe(string|array $channels, callable $callback): bool
    {
        $channels = (array)$channels;
        foreach ($channels as $channel) {
            $this->callbacks['message'][$channel] = $callback;
        }
        return $this->conn->sendCommand('channel:subscribe', $channels);
    }

    /**
     * @param string|array $channels
     * @return bool
     */
    public function unsubscribe(string|array $channels): bool
    {
        return $this->conn->sendCommand('channel:unsubscribe', $channels);
    }

    /**
     * @param string|array $channels
     * @param string $message
     * @return bool
     */
    public function publish(string|array $channels, string $message): bool
    {
        return $this->conn->publish($channels, $message);
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function channels(callable $callback): bool
    {
        $this->callbacks[__FUNCTION__][] = $callback;
        return $this->conn->sendCommand('channel:channels');
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->conn->sendCommand('channel:close');
    }
}