<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\ContextInterface;

class Context implements ContextInterface
{
    /**
     * @var array
     */
    protected array $context = [];

    /**
     * @param string $id
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $id, mixed $default = null): mixed
    {
        return $this->context[$id] ?? null;
    }

    /**
     * @param string $id
     * @param mixed $value
     * @return mixed
     */
    public function set(string $id, mixed $value): mixed
    {
        $this->context[$id] = $value;
        return $value;
    }

    /**
     * @param string $id
     * @param \Closure $closure
     * @return mixed
     */
    public function remember(string $id, \Closure $closure): mixed
    {
        if (!$this->has($id)) {
            return $this->set($id, $closure());
        }

        return $this->get($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->context[$id]);
    }

    /**
     * @param string $id
     * @return void
     */
    public function destroy(string $id): void
    {
        unset($this->context[$id]);
    }
}