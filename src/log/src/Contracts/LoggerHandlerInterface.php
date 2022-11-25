<?php

declare(strict_types=1);

namespace Larmias\Log\Contracts;

interface LoggerHandlerInterface
{
    /**
     * @param array $logs
     * @return bool
     */
    public function save(array $logs): bool;
}