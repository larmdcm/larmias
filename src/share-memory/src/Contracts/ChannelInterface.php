<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Contracts;

interface ChannelInterface
{
    public function subscribe(string|array $channels, int $id): array;

    public function unsubscribe(string|array $channels, int $id): array;

    public function publish(string|array $channels): array;

    public function channels(int $id): array;

    public function close(int $id): array;
}