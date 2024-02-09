<?php

declare(strict_types=1);

namespace Larmias\Support\Traits;

use function call_user_func;
use function explode;
use function is_string;
use function str_contains;

trait HasEvents
{
    /**
     * @var array
     */
    protected array $events = [];

    /**
     * @param string|array $name
     * @param callable $callback
     * @return self
     */
    public function on(string|array $name, callable $callback): self
    {
        if (is_string($name)) {
            $name = str_contains($name, ',') ? explode(',', $name) : [$name];
        }
        foreach ($name as $item) {
            $this->events[$item] = $callback;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasListen(string $name): bool
    {
        return isset($this->events[$name]);
    }

    /**
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     */
    public function fireEvent(string $name, ...$args): mixed
    {
        if (!$this->hasListen($name)) {
            return false;
        }
        return call_user_func($this->events[$name], ...$args);
    }
}