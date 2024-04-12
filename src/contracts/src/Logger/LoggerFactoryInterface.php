<?php

declare(strict_types=1);

namespace Larmias\Contracts\Logger;

use Larmias\Contracts\LoggerInterface;

interface LoggerFactoryInterface
{
    /**
     * @param string|null $name
     * @return LoggerInterface
     */
    public function make(?string $name = null): LoggerInterface;
}