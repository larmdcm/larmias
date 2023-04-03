<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\SharedMemory\Contracts\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use function Larmias\Utils\println;

class Logger implements LoggerInterface
{
    /**
     * @param WorkerInterface $worker
     * @param PsrLoggerInterface $logger
     */
    public function __construct(protected WorkerInterface $worker, protected PsrLoggerInterface $logger)
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
            println($message);
        }

        if ($this->worker->getSettings('log_record', false)) {
            $this->logger->log($level, $message, $context);
        }
    }
}