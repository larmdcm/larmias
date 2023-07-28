<?php

declare(strict_types=1);

namespace Larmias\Database\Providers;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Manager;
use Larmias\Database\Model\AbstractModel;
use Psr\EventDispatcher\EventDispatcherInterface;

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
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/database.php' => $app->getConfigPath() . 'database.php',
            ]);
        }

        /** @var ManagerInterface $manager */
        $manager = $this->container->make(ManagerInterface::class, ['config' => $this->config->get('database', [])]);
        $manager->setEventDispatcher($this->container->get(EventDispatcherInterface::class));

        AbstractModel::maker(function (AbstractModel $model) use ($manager) {
            $model->setManager($manager);
        });
    }
}