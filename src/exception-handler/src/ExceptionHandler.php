<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ExceptionReportHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use function get_class;

abstract class ExceptionHandler implements ExceptionReportHandlerInterface
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