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
     * @param array $args
     * @return mixed
     */
    public function handle(Throwable $e, array $args = []): mixed
    {
        return null;
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