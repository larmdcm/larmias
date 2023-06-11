<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ExceptionHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use function get_class;

abstract class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array
     */
    protected array $dontReport = [];

    /**
     * @var array
     */
    protected array $levels = [];

    /**
     * @var bool
     */
    protected bool $isPropagationStopped = false;

    /**
     * ExceptionHandler constructor.
     *
     * @param ContainerInterface $container
     * @param LoggerInterface|null $logger
     */
    public function __construct(protected ContainerInterface $container, protected ?LoggerInterface $logger = null)
    {
    }

    /**
     * 异常记录.
     *
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e): void
    {
        if ($this->isDontReport($e)) {
            return;
        }
        try {
            $this->logger?->log($this->levels[get_class($e)] ?? 'error', $e->getMessage(), ['exception' => $e->getTraceAsString()]);
        } catch (Throwable) {
        }
    }

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

    /**
     * @param Throwable $exception
     * @return boolean
     */
    protected function isDontReport(Throwable $exception): bool
    {
        foreach ($this->dontReport as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }
        return false;
    }
}