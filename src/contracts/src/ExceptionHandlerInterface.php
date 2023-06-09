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
     * @param array $args
     * @return mixed
     */
    public function handle(Throwable $e, array $args = []): mixed;

    /**
     * @param Throwable $e
     * @return bool
     */
    public function isValid(Throwable $e): bool;
}