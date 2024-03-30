<?php

declare(strict_types=1);

namespace Larmias\Phar\Commands;

use Larmias\Framework\Commands\Command;
use Larmias\Contracts\ConfigInterface;
use Larmias\Phar\Builder;

class BuildCommand extends Command
{
    /**
     * @var string
     */
    protected string $name = 'phar:build';

    public function handle(): void
    {
        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);
        $pharConfig = $config->get('phar', []);
        $builder = new Builder($pharConfig);
        $builder->build();
        $this->output->writeln('Write requests to the Phar archive, save changes to disk.');
    }
}