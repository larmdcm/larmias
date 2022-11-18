<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Concerts;

trait HasEvents
{
    /**
     * @var array
     */
    protected array $events = [];

    /**
     * @param string   $name
     * @param callable $callback
     * @return self
     */
    public function on(string $name,callable $callback): self
    {
        $binds = \str_contains($name, ',') ? \explode(',',$name) : [$name];
        foreach ($binds as $item) {
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
     * @param string   $name
     * @param mixed ...$args
     * @return mixed
     */
    public function fireEvent(string $name,...$args): mixed
    {
        if (!$this->hasListen($name)) {
            return false;
        }
        return call_user_func($this->events[$name],...$args);
    }
}