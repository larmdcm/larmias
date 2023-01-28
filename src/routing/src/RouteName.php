<?php

declare(strict_types=1);

namespace Larmias\Routing;

class RouteName
{
    protected array $rules = [];

    public function set(string $name, int $index): void
    {
        $this->rules[$name] = $index;
    }

    public function get(string $name): int
    {
        return $this->rules[$name] ?? -1;
    }
}