<?php

declare(strict_types=1);

namespace Larmias\Routing;

class RouteName
{
    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * @param string $name
     * @param int $index
     * @return void
     */
    public function set(string $name, int $index): void
    {
        $this->rules[$name] = $index;
    }

    /**
     * @param string $name
     * @return int
     */
    public function get(string $name): int
    {
        return $this->rules[$name] ?? -1;
    }
}