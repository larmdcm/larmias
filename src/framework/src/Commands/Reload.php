<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Symfony\Component\Console\Input\InputOption;

class Reload extends Worker
{
    /**
     * @var string
     */
    protected string $name = 'reload';

    /**
     * @var string
     */
    protected string $description = 'Reload larmias workers.';

    /**
     * @return void
     */
    public function configure(): void
    {
        parent::configure();
        $this->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'force reload worker', true);
    }

    /**
     * @return void
     */
    protected function handle(): void
    {
        $this->kernel->reload($this->input->getOption('force'));
    }
}