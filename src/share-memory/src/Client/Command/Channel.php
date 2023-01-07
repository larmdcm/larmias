<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client\Command;

use Larmias\Engine\EventLoop;
use Larmias\ShareMemory\Client\Client;

class Channel extends Command
{
    protected array $callbacks = [];

    protected function initialize(): void
    {
        $this->client = $this->client->clone([
            'auto_connect' => true,
            'event' => [
                Client::EVENT_CONNECT => [$this, 'onConnect']
            ]
        ]);
    }

    public function onConnect(Client $client): void
    {
        $socket = $client->getSocket();
        \stream_set_blocking($socket, false);
        \stream_set_write_buffer($socket, 0);
        \stream_set_read_buffer($socket, 0);
        EventLoop::onReadable($socket, function () use ($client) {
            $result = $client->read();
            if (!$result || !$result->success || !\is_array($result->data) || !isset($result->data['type'])) {
                return;
            }
            switch ($result->data['type']) {
                case 'message':
                    if (isset($this->callbacks['message'][$result->data['channel']])) {
                        \call_user_func($this->callbacks['message'][$result->data['channel']], $result->data);
                    }
                    break;
                case 'channels':
                    if (isset($this->callbacks['channels'])) {
                        foreach ($this->callbacks['channels'] as $callback) {
                            \call_user_func($callback, $result->data);
                        }
                    }
                    break;
            }
        });
    }

    public function subscribe(string|array $channels, callable $callback): bool
    {
        $channels = (array)$channels;
        foreach ($channels as $channel) {
            $this->callbacks['message'][$channel] = $callback;
        }
        return $this->client->sendCommand('channel:subscribe', $channels);
    }

    public function unsubscribe(string|array $channels): bool
    {
        return $this->client->sendCommand('channel:unsubscribe', $channels);
    }

    public function publish(string|array $channels, string $message): bool
    {
        return $this->client->sendCommand('channel:publish', [$channels, $message]);
    }

    public function channels(callable $callback): bool
    {
        $this->callbacks[__FUNCTION__][] = $callback;
        return $this->client->sendCommand('channel:channels');
    }

    public function close(): bool
    {
        return $this->client->sendCommand('channel:close');
    }
}