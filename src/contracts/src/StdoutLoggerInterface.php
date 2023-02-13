<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface StdoutLoggerInterface extends LoggerInterface
{
    /**
     * @param string $message
     * @return void
     */
    public function write(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function writeln(string $message): void;
}