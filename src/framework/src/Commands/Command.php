<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Larmias\Command\Command as BaseCommand;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Larmias\Framework\run;

abstract class Command extends BaseCommand
{
    /**
     * @var KernelInterface
     */
    protected KernelInterface $kernel;

    /**
     * @var WorkerInterface
     */
    protected WorkerInterface $worker;

    /**
     * @var bool
     */
    protected bool $inEngineContainer = true;

    /**
     * @param ApplicationInterface $app
     */
    public function __construct(protected ApplicationInterface $app)
    {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->inEngineContainer) {
            return parent::execute($input, $output);
        }
        run(function ($worker, $kernel) use ($input, $output) {
            $this->worker = $worker;
            $this->kernel = $kernel;
            parent::execute($input, $output);
        });
        return self::SUCCESS;
    }
}