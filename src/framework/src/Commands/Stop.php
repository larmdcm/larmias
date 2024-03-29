<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Symfony\Component\Console\Input\InputOption;

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
    public function configure(): void
    {
        parent::configure();
        $this->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'force stop worker', true);
    }

    /**
     * @return void
     */
    protected function handle(): void
    {
        $this->kernel->stop($this->input->getOption('force'));
    }
}