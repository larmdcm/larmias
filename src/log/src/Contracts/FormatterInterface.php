<?php

declare(strict_types=1);

namespace Larmias\Log\Contracts;

interface FormatterInterface
{
    public function format(string|\Stringable $message, string $channel, string $level, array $context = []): string;
}