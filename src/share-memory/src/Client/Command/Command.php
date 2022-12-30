<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client\Command;

use Larmias\ShareMemory\Client\Client;

abstract class Command
{
    public function __construct(protected Client $client)
    {
    }
}