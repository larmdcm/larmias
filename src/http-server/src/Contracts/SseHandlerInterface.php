<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Stringable;

interface SseHandlerInterface
{
    /**
     * @param string|Stringable $data
     * @return bool
     */
    public function write(string|Stringable $data): bool;

    /**
     * @param string $data
     * @return void
     */
    public function end(string $data = ''): void;

    /**
     * @return bool
     */
    public function isEnd(): bool;
}