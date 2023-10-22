<?php

declare(strict_types=1);

namespace Larmias\Testing;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\EngineConfig;

class Run
{
    protected array $config = [];

    public function __construct(protected ContainerInterface $container)
    {
        $file = $this->container->get(ApplicationInterface::class)->getConfigPath() . 'engine.php';
        $this->config = require $file;
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function __invoke(): int
    {
        $kernel = new Kernel($this->container);

        $kernel->setConfig(EngineConfig::build($this->config));

        $kernel->run();

        return 0;
    }
}