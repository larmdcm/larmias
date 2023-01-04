<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client\Command;

use Larmias\Engine\EventLoop;

class Channel extends Command
{
    protected array $channels = [];

    protected function initialize(): void
    {
        $this->client = $this->client->clone();
        $socket = $this->client->getSocket();
        \stream_set_blocking($socket, false);
        \stream_set_read_buffer($socket, 0);
        EventLoop::onReadable($socket, function () {
            $result = $this->client->read();
            if (!$result || !$result->success) {
                return;
            }
            if (\is_string($result->data)) {
                return;
            }
            dump($result->data);
        });
    }

    public function subscribe(string|array $channels, callable $callback): self
    {
        $channels = (array)$channels;
        $this->client->command('channel:subscribe', $channels);
        foreach ($channels as $channel) {
            $this->channels[$channel] = $callback;
        }
        return $this;
    }
}