<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

class Start extends Worker
{
    /**
     * @var string
     */
    protected string $name = 'start';

    /**
     * @var string
     */
    protected string $description = 'Start larmias workers.';

    /**
     * @return void
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->kernel->run();
    }
}