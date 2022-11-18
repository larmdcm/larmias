<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Psr\Log\LoggerInterface as BaseLoggerInterface;

interface LoggerInterface extends BaseLoggerInterface
{
    /**
     * Sql log info.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function sql(string|\Stringable $message, array $context = []): void;

}