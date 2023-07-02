<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler;

use Larmias\Contracts\ExceptionHandlerInterface;
use Throwable;
use function get_class;

abstract class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var bool
     */
    protected bool $isPropagationStopped = false;

    /**
     * @param Throwable $e
     * @param mixed $result
     * @param mixed|null $args
     * @return mixed
     */
    public function handle(Throwable $e, mixed $result, mixed $args = null): mixed
    {
        return $result;
    }

    /**
     * @param Throwable $e
     * @return bool
     */
    public function isValid(Throwable $e): bool
    {
        return true;
    }

    /**
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}