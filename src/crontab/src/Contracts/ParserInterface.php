<?php

declare(strict_types=1);

namespace Larmias\Crontab\Contracts;

interface ParserInterface
{
    /**
     * @param string $rule
     * @param mixed|null $startTime
     * @return int[]
     */
    public function parse(string $rule, mixed $startTime = null): array;

    /**
     * @param string $rule
     * @return bool
     */
    public function isValid(string $rule): bool;
}