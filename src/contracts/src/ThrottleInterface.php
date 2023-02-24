<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ThrottleInterface
{
    /**
     * @param string $key
     * @param array $rateLimit
     * @return bool
     */
    public function allow(string $key, array $rateLimit = []): bool;

    /**
     * @return array
     */
    public function getAllowInfo(): array;
}