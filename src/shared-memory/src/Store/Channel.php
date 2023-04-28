<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Store;

use Larmias\SharedMemory\Contracts\ChannelInterface;
use function array_keys;
use function array_filter;

class Channel implements ChannelInterface
{
    /**
     * @var array
     */
    protected array $channels = [];

    /**
     * @param string|array $channels
     * @param int $id
     * @return array
     */
    public function subscribe(string|array $channels, int $id): array
    {
        foreach ((array)$channels as $name) {
            $this->channels[$name][$id] = $id;
        }
        return $this->channels($id);
    }

    /**
     * @param string|array $channels
     * @param int $id
     * @return array
     */
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

    /**
     * @param string|array $channels
     * @return array
     */
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

    /**
     * @param int $id
     * @return array
     */
    public function channels(int $id): array
    {
        return array_keys(array_filter($this->channels, fn($item) => isset($item[$id])));
    }

    /**
     * @param int $id
     * @return bool
     */
    public function close(int $id): bool
    {
        $this->unsubscribe(array_keys($this->channels), $id);
        return true;
    }
}