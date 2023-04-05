<?php

declare(strict_types=1);

namespace Larmias\Database\Providers;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Database\Manager;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(ManagerInterface::class, Manager::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        /** @var ManagerInterface $manager */
        $manager = $this->container->make(ManagerInterface::class, ['config' => $this->config->get('database', [])]);
        $manager->setEventDispatcher($this->container->get(EventDispatcherInterface::class));

        Model::maker(function (Model $model) use ($manager) {
            $model->setManager($manager);
        });
    }
}