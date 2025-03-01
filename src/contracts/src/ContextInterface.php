<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Closure;

interface ContextInterface
{
    /**
     * @param string $id
     * @param mixed|null $default
     * @param int|null $cid
     * @return mixed
     */
    public function get(string $id, mixed $default = null, ?int $cid = null): mixed;

    /**
     * @param string $id
     * @param mixed $value
     * @param int|null $cid
     * @return mixed
     */
    public function set(string $id, mixed $value, ?int $cid = null): mixed;

    /**
     * @param string $id
     * @param Closure $closure
     * @param int|null $cid
     * @return mixed
     */
    public function remember(string $id, Closure $closure, ?int $cid = null): mixed;

    /**
     * @param string $id
     * @param int|null $cid
     * @return bool
     */
    public function has(string $id, ?int $cid = null): bool;

    /**
     * @param string $id
     * @param int|null $cid
     * @return void
     */
    public function destroy(string $id, ?int $cid = null): void;

    /**
     * @return bool
     */
    public function inCoroutine(): bool;

    /**
     * @return bool
     */
    public function inFiber(): bool;
}