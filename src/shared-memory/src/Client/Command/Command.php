<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\SharedMemory\Client\Client;

abstract class Command
{
    /**
     * @param Client $client
     */
    public function __construct(protected Client $client)
    {
        $this->initialize();
    }

    /**
     * @return void
     */
    protected function initialize(): void
    {
    }
}