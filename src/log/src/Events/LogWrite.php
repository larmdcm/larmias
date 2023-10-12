<?php

declare(strict_types=1);

namespace Larmias\Log\Events;

class LogWrite
{
    public function __construct(public array $logs, public string $name)
    {
    }
}