<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Symfony\Component\Console\Input\InputOption;

class Restart extends Worker
{
    /**
     * @var string
     */
    protected string $name = 'restart';

    /**
     * @var string
     */
    protected string $description = 'Restart larmias workers.';

    /**
     * @return void
     */
    public function configure(): void
    {
        parent::configure();
        $this->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'force restart worker', true);
    }

    /**
     * @return void
     */
    protected function handle(): void
    {
        $this->kernel->restart($this->input->getOption('force'));
    }
}