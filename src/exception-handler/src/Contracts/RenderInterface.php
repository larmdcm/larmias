<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Contracts;

use Throwable;

interface RenderInterface
{
    public function addDataTableCallback(string $name, callable $callback): RenderInterface;

    public function render(Throwable $e): string;
}