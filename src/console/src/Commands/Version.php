<?php

declare(strict_types=1);

namespace Larmias\Console\Commands;

use Larmias\Console\Command;

class Version extends Command
{
    public function configure(): void
    {
        $this->setName('version')
            ->setDescription('Show larmias console version');
    }

    public function handle(): int
    {
        $this->output->writeln('v' . $this->console->getVersion());
        return self::SUCCESS;
    }
}