<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\ContextInterface;
use RuntimeException;
use Workerman\Coroutine\Context as WorkermanContext;
use Workerman\Events\Fiber;

class Context implements ContextInterface
{
    /**
     * @param string $id
     * @param mixed|null $default
     * @param int|null $cid
     * @return mixed
     */
    public function get(string $id, mixed $default = null, ?int $cid = null): mixed
    {
        if ($cid !== null) {
            throw new RuntimeException('not support cid:' . $cid);
        }

        return WorkermanContext::get($id, $default);
    }

    /**
     * @param string $id
     * @param mixed $value
     * @param int|null $cid
     * @return mixed
     */
    public function set(string $id, mixed $value, ?int $cid = null): mixed
    {
        if ($cid !== null) {
            throw new RuntimeException('not support cid:' . $cid);
        }

        WorkermanContext::set($id, $value);
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
        if ($cid !== null) {
            throw new RuntimeException('not support cid:' . $cid);
        }

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
        if ($cid !== null) {
            throw new RuntimeException('not support cid:' . $cid);
        }

        return WorkermanContext::has($id);
    }

    /**
     * @param string $id
     * @param int|null $cid
     * @return void
     */
    public function destroy(string $id, ?int $cid = null): void
    {
        if ($cid !== null) {
            throw new RuntimeException('not support cid:' . $cid);
        }

        $this->set($id, null);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        WorkermanContext::destroy();
    }

    /**
     * @return bool
     */
    public function inCoroutine(): bool
    {
        return \Workerman\Coroutine::isCoroutine();
    }

    /**
     * @return bool
     */
    public function inFiber(): bool
    {
        return get_class(Worker::getEventLoop()) === Fiber::class;
    }
}