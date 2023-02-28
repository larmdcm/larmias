<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

class Stop extends Worker
{
    /**
     * @var string
     */
    protected string $name = 'stop';

    /**
     * @var string
     */
    protected string $description = 'Stop larmias workers.';

    /**
     * @return void
     */
    protected function handle(): void
    {
    }
}