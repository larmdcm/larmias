<?php

declare(strict_types=1);

namespace Larmias\Console\Commands;

use Larmias\Console\Command;

class Help extends Command
{
    /** @var string  */
    protected string $name = 'help';

    protected string $description = '';

    /**
     * @return void
     */
    public function handle(): void
    {
    }
}