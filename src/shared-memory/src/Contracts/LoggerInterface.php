<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

interface LoggerInterface
{
    /**
     * @param string|\Stringable $message
     * @param string $level
     * @param array $context
     * @return void
     */
    public function trace(string|\Stringable $message, string $level = 'debug', array $context = []): void;
}