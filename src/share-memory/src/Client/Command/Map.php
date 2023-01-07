<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client\Command;

class Map extends Command
{
    public function set(string $key, string $value): bool
    {
        $result = $this->client->command('map:set', [$key, $value]);
        return $result && $result->success;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $result = $this->client->command('map:get', [$key]);
        return $result && $result->success ? $result->data : $default;
    }

    public function delete(string $key): bool
    {
        $result = $this->client->command('map:delete', [$key]);
        return $result && $result->success;
    }

    public function exists(string $key): bool
    {
        $result = $this->client->command('map:exists', [$key]);
        return $result && $result->success ? $result->data : false;
    }

    public function clear(): bool
    {
        $result = $this->client->command('map:clear');
        return $result && $result->success;
    }

    public function count(): ?int
    {
        $result = $this->client->command('map:count');
        return $result && $result->success ? (int)$result->data : null;
    }
}