<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\SharedMemory\Client\Client;

abstract class Command
{
    public function __construct(protected Client $client)
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
    }
}