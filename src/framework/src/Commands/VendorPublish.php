<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Larmias\Contracts\VendorPublishInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Input\InputOption;

class VendorPublish extends Command
{
    /**
     * @var string
     */
    protected string $name = 'vendor:publish';

    /**
     * @var string
     */
    protected string $description = 'Publish any publishable assets from vendor packages';

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(): void
    {
        /** @var VendorPublishInterface $publish */
        $publish = $this->app->getContainer()->get(VendorPublishInterface::class);
        $provider = $this->input->getOption('provider');
        $force = $this->input->getOption('force');
        $publish->handle($provider, $force);
        $this->output->info('Succeed!');
        $this->exit();
    }

    /**
     * @return array[]
     */
    public function getOptions(): array
    {
        return [
            ['provider', 'p', InputOption::VALUE_OPTIONAL, 'Specify the owning provider'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite any existing files'],
        ];
    }
}