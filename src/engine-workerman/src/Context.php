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
     * @param int|null $cid
     * @return mixed
     */
    public function get(string $id, mixed $default = null, ?int $cid = null): mixed
    {
        return $this->context[$id] ?? $default;
    }

    /**
     * @param string $id
     * @param mixed $value
     * @param int|null $cid
     * @return mixed
     */
    public function set(string $id, mixed $value, ?int $cid = null): mixed
    {
        $this->context[$id] = $value;
        return $value;
    }

    /**
     * @param string $id
     * @param \Closure $closure
     * @param int|null $cid
     * @return mixed
     */
    public function remember(string $id, \Closure $closure, ?int $cid = null): mixed
    {
        if (!$this->has($id)) {
            return $this->set($id, $closure());
        }

        return $this->get($id);
    }

    /**
     * @param string $id
     * @param int|null $cid
     * @return bool
     */
    public function has(string $id, ?int $cid = null): bool
    {
        return isset($this->context[$id]);
    }

    /**
     * @param string $id
     * @param int|null $cid
     * @return void
     */
    public function destroy(string $id, ?int $cid = null): void
    {
        unset($this->context[$id]);
    }

    /**
     * @return bool
     */
    public function inCoroutine(): bool
    {
        return false;
    }
}