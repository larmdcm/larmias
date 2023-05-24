<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Larmias\Framework\Commands\Concerns\WorkerConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Contracts\KernelInterface;

abstract class Worker extends Command
{
    use WorkerConfig;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

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
        $this->setName($this->name)->setDescription($this->description);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->makeKernel();
        $this->handle();
        return self::SUCCESS;
    }

    /**
     * @return void
     */
    abstract protected function handle(): void;

    /**
     * @return void
     */
    protected function makeKernel(): void
    {
        $this->kernel->setConfig(EngineConfig::build($this->getWorkerConfig()));
    }
}