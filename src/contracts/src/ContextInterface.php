<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ContextInterface
{
    /**
     * @param string $id
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $id, mixed $default = null): mixed;

    /**
     * @param string $id
     * @param mixed $value
     * @return mixed
     */
    public function set(string $id, mixed $value): mixed;

    /**
     * @param string $id
     * @param \Closure $closure
     * @return mixed
     */
    public function remember(string $id, \Closure $closure): mixed;

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param string $id
     * @return void
     */
    public function destroy(string $id): void;
}