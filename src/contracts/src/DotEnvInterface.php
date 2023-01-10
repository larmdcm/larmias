<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface DotEnvInterface
{
    public function load(string $path): bool;

    public function get(string $name, mixed $default = null): mixed;

    public function set(string|array $name, ?string $value = null): void;

    public function has(string $name): bool;
}