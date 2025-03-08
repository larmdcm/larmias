<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Closure;

class Context implements ContextInterface
{
    /**
     * @param CoroutineInterface $coroutine
     */
    public function __construct(protected CoroutineInterface $coroutine)
    {
    }

    /**
     * @param string $id
     * @param mixed|null $default
     * @param int|null $cid
     * @return mixed
     */
    public function get(string $id, mixed $default = null, ?int $cid = null): mixed
    {
        return $this->coroutine->getContextFor($cid)[$id] ?? $default;
    }

    /**
     * @param string $id
     * @param mixed $value
     * @param int|null $cid
     * @return mixed
     */
    public function set(string $id, mixed $value, ?int $cid = null): mixed
    {
        $this->coroutine->getContextFor($cid)[$id] = $value;
        return $value;
    }

    /**
     * @param string $id
     * @param Closure $closure
     * @param int|null $cid
     * @return mixed
     */
    public function remember(string $id, Closure $closure, ?int $cid = null): mixed
    {
        if (!$this->has($id, $cid)) {
            return $this->set($id, $closure(), $cid);
        }

        return $this->get($id, cid: $cid);
    }

    /**
     * @param string $id
     * @param int|null $cid
     * @return bool
     */
    public function has(string $id, ?int $cid = null): bool
    {
        return isset($this->coroutine->getContextFor($cid)[$id]);
    }

    /**
     * @param string $id
     * @param int|null $cid
     * @return void
     */
    public function destroy(string $id, ?int $cid = null): void
    {
        $this->set($id, null, $cid);
    }

    /**
     * @return bool
     */
    public function inCoroutine(): bool
    {
        return $this->coroutine->id() > 0;
    }

    /**
     * @return bool
     */
    public function inFiber(): bool
    {
        return false;
    }
}