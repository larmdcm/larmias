<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Contracts\KernelInterface;
use RuntimeException;

abstract class Worker extends Command
{
    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $description = '';

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
        if (\method_exists($this->app, 'loadFileConfig')) {
            $config = $this->app->loadFileConfig('worker');
        } else {
            $configFile = $this->app->getConfigPath() . 'worker.php';
            if (!\is_file($configFile)) {
                throw new RuntimeException(sprintf('%s The worker configuration file does not exist.', $configFile));
            }
            $config = require $configFile;
        }
        $this->kernel->setConfig(EngineConfig::build($config));
    }
}