<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Larmias\Console\Command;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Contracts\KernelInterface;
use RuntimeException;

class Start extends Command
{
    public function __construct(protected ApplicationInterface $app, protected KernelInterface $kernel)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('start')
            ->setDescription('Start larmias workers.');
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function handle(): int
    {
        $configFile = $this->app->getConfigPath() . 'worker.php';

        if (!is_file($configFile)) {
            throw new RuntimeException(sprintf('%s The worker configuration file does not exist.', $configFile));
        }

        $this->kernel->setConfig(EngineConfig::build(require $configFile));

        $this->kernel->run();

        return self::SUCCESS;
    }
}