<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\SharedMemory\Client\Client;
use function call_user_func;
use function is_array;

class Channel extends Command
{
    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->client = $this->client->clone([
            'auto_connect' => true,
            'async' => true,
            'event' => [
                Client::EVENT_CONNECT => [$this, 'onConnect']
            ]
        ]);
    }

    /**
     * @param Client $client
     * @return void
     */
    public function onConnect(Client $client): void
    {
        Client::getEventLoop()->onReadable($client->getSocket(), function () use ($client) {
            $result = $client->read();
            if (!$result) {
                $client->close();
                return;
            }
            if (!$result->success || !is_array($result->data) || !isset($result->data['type'])) {
                return;
            }
            switch ($result->data['type']) {
                case 'message':
                    if (isset($this->callbacks['message'][$result->data['channel']])) {
                        call_user_func($this->callbacks['message'][$result->data['channel']], $result->data);
                    }
                    break;
                case 'channels':
                    if (isset($this->callbacks['channels'])) {
                        foreach ($this->callbacks['channels'] as $callback) {
                            call_user_func($callback, $result->data);
                        }
                    }
                    break;
            }
        });
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
        return $this->client->sendCommand('channel:subscribe', $channels);
    }

    /**
     * @param string|array $channels
     * @return bool
     */
    public function unsubscribe(string|array $channels): bool
    {
        return $this->client->sendCommand('channel:unsubscribe', $channels);
    }

    /**
     * @param string|array $channels
     * @param string $message
     * @return bool
     */
    public function publish(string|array $channels, string $message): bool
    {
        return $this->client->sendCommand('channel:publish', [$channels, $message]);
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function channels(callable $callback): bool
    {
        $this->callbacks[__FUNCTION__][] = $callback;
        return $this->client->sendCommand('channel:channels');
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->client->sendCommand('channel:close');
    }
}