<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\Client\AsyncSocket;
use Larmias\Codec\Packer\FramePacker;
use Larmias\Contracts\Client\AsyncSocketInterface;
use Larmias\SharedMemory\Client\Client;
use Larmias\SharedMemory\Message\Result;

class Channel extends Command
{
    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * @var AsyncSocketInterface
     */
    protected AsyncSocketInterface $asyncSocket;

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->client = $this->client->clone([
            'auto_connect' => true,
            'async' => true,
            'event' => [
                Client::EVENT_CONNECT => fn(Client $client) => $this->onConnect($client)
            ]
        ]);
    }

    /**
     * @param Client $client
     * @return void
     */
    protected function onConnect(Client $client): void
    {
        $this->asyncSocket = new AsyncSocket(Client::getEventLoop(), $client->getSocket());
        $this->asyncSocket->set([
            'packer_class' => FramePacker::class,
        ]);
        $this->asyncSocket->on(AsyncSocketInterface::ON_MESSAGE, function (mixed $data) {
            $result = Result::parse($data);
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