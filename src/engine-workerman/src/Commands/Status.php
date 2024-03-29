<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\Contracts\KernelInterface;

class Status extends Command
{
    /**
     * @var string
     */
    protected string $name = 'workerman:status';

    /**
     * @var string
     */
    protected string $description = 'Show workerman worker status.';

    /**
     * @param ApplicationInterface $app
     * @param KernelInterface $kernel
     */
    public function __construct(protected ApplicationInterface $app, protected KernelInterface $kernel)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        $this->setName($this->name)
            ->setDescription($this->description);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $globalArgv = $GLOBALS['argv'];
        $GLOBALS['argv'][] = 'status';
        $this->getApplication()->find('start')->run(new ArrayInput([]), $output);
        $GLOBALS['argv'] = $globalArgv;
        return self::SUCCESS;
    }
}