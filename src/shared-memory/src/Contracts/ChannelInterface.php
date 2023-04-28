<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

interface ChannelInterface
{
    /**
     * @param string|array $channels
     * @param int $id
     * @return array
     */
    public function subscribe(string|array $channels, int $id): array;

    /**
     * @param string|array $channels
     * @param int $id
     * @return array
     */
    public function unsubscribe(string|array $channels, int $id): array;

    /**
     * @param string|array $channels
     * @return array
     */
    public function publish(string|array $channels): array;

    /**
     * @param int $id
     * @return array
     */
    public function channels(int $id): array;

    /**
     * @param int $id
     * @return bool
     */
    public function close(int $id): bool;
}