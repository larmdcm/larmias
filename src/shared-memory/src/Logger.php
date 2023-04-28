<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\SharedMemory\Contracts\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use function Larmias\Utils\println;

class Logger implements LoggerInterface
{
    /**
     * @param WorkerInterface $worker
     * @param PsrLoggerInterface $logger
     * @param StdoutLoggerInterface|null $stdoutLogger
     */
    public function __construct(protected WorkerInterface $worker, protected PsrLoggerInterface $logger, protected ?StdoutLoggerInterface $stdoutLogger = null)
    {
    }

    /**
     * @param \Stringable|string $message
     * @param string $level
     * @param array $context
     * @return void
     */
    public function trace(\Stringable|string $message, string $level = 'debug', array $context = []): void
    {
        if ($this->worker->getSettings('console_output', true)) {
            $this->stdoutLogger ? $this->stdoutLogger->log($level, $message, $context) :
                println(sprintf('[%s]%s %s', $level, $message, $context ? var_export($context, true) : ''));
        }

        if ($this->worker->getSettings('log_record', false)) {
            $this->logger->log($level, $message, $context);
        }
    }
}