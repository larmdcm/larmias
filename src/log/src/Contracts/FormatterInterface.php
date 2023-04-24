<?php

declare(strict_types=1);

namespace Larmias\Log\Contracts;

interface FormatterInterface
{
    /**
     * @param string|\Stringable $message
     * @param string $channel
     * @param string $level
     * @param array $context
     * @return string
     */
    public function format(string|\Stringable $message, string $channel, string $level, array $context = []): string;
}