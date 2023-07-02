<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler;

use Psr\Log\LoggerInterface;
use Throwable;

class ReportExceptionHandler extends ExceptionHandler
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
     * @param LoggerInterface|null $logger
     */
    public function __construct(protected ?LoggerInterface $logger = null)
    {
    }

    /**
     * @param Throwable $e
     * @param mixed $result
     * @param mixed|null $args
     * @return mixed
     */
    public function handle(Throwable $e, mixed $result, mixed $args = null): mixed
    {
        $this->report($e);
        return $result;
    }

    /**
     * 异常上报.
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