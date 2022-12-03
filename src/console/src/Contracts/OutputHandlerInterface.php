<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

interface OutputHandlerInterface
{
    /**
     * @param string|array $messages
     * @param bool $newline
     * @param int $type
     * @return void
     */
    public function write(string|array $messages, bool $newline = false, int $type = 0): void;
}