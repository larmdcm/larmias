<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface DotEnvInterface
{
    /**
     * @param string $path
     * @return bool
     */
    public function load(string $path): bool;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function set(string|array $name, ?string $value = null): void;

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
}