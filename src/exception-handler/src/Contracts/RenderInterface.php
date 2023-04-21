<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Contracts;

use Throwable;

interface RenderInterface
{
    /**
     * @param string $name
     * @param callable $callback
     * @return RenderInterface
     */
    public function addDataTableCallback(string $name, callable $callback): RenderInterface;

    /**
     * @param Throwable $e
     * @return string
     */
    public function render(Throwable $e): string;
}