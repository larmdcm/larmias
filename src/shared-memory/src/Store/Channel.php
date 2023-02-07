<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Store;

use Larmias\SharedMemory\Contracts\ChannelInterface;

class Channel implements ChannelInterface
{
    protected array $channels = [];

    public function subscribe(string|array $channels, int $id): array
    {
        foreach ((array)$channels as $name) {
            $this->channels[$name][$id] = $id;
        }
        return $this->channels($id);
    }

    public function unsubscribe(string|array $channels, int $id): array
    {
        foreach ((array)$channels as $name) {
            if (isset($this->channels[$name][$id])) {
                unset($this->channels[$name][$id]);
            }
            if (isset($this->channels[$name]) && empty($this->channels[$name])) {
                unset($this->channels[$name]);
            }
        }

        return $this->channels($id);
    }

    public function publish(string|array $channels): array
    {
        $result = [];
        foreach ((array)$channels as $name) {
            if (!isset($this->channels[$name])) {
                continue;
            }
            $result[] = ['id' => $this->channels[$name], 'channel' => $name];
        }
        return $result;
    }

    public function channels(int $id): array
    {
        return \array_keys(\array_filter($this->channels, fn($item) => isset($item[$id])));
    }

    public function close(int $id): bool
    {
        $this->unsubscribe(\array_keys($this->channels), $id);
        return true;
    }
}