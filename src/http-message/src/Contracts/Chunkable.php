<?php

declare(strict_types=1);

namespace Larmias\Http\Message\Contracts;

interface Chunkable
{
    /**
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool;
}