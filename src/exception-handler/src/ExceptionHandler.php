<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ExceptionReportHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class ExceptionHandler implements ExceptionReportHandlerInterface
{
    /**
     * @var array
     */
    protected array $ignoreReport = [];

    /**
     * ExceptionHandler constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
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
        if ($this->isIgnoreReport($e)) {
            return;
        }
        try {
            if ($this->container->has(LoggerInterface::class)) {
                /** @var LoggerInterface $logger */
                $logger = $this->container->get(LoggerInterface::class);
                $logger->error($e->getMessage(), ['exception' => $e->getTraceAsString()]);
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * @param Throwable $exception
     * @return boolean
     */
    protected function isIgnoreReport(Throwable $exception): bool
    {
        foreach ($this->ignoreReport as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }
        return false;
    }

    /**
     * 收集异常信息
     *
     * @param Throwable $exception
     * @return array
     */
    protected function collectExceptionToArray(Throwable $exception): array
    {
        $traces = [];
        $nextException = $exception;
        do {
            $traces[] = [
                'name' => \get_class($nextException),
                'file' => $nextException->getFile(),
                'line' => $nextException->getLine(),
                'code' => $nextException->getCode(),
                'message' => $nextException->getMessage(),
                'trace' => $nextException->getTrace(),
            ];
        } while ($nextException = $nextException->getPrevious());
        $data = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'traces' => $traces,
        ];
        return $data;
    }
}