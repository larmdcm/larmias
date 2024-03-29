<?php

declare(strict_types=1);

namespace Larmias\Phar\Commands;

use Larmias\Command\Command;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;

class BuildCommand extends Command
{
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        parent::__construct('phar:build');
    }

    public function handle(): void
    {
        $pharConfig = $this->config->get('phar');
    }
}