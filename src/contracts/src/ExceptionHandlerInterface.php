<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e): void;

    /**
     * @param Throwable $e
     * @param mixed $result
     * @param mixed $args
     * @return mixed
     */
    public function handle(Throwable $e, mixed $result, mixed $args = null): mixed;

    /**
     * @param Throwable $e
     * @return bool
     */
    public function isValid(Throwable $e): bool;

    /**
     * @return void
     */
    public function stopPropagation(): void;

    /**
     * @return bool
     */
    public function isPropagationStopped(): bool;
}