<?php

declare(strict_types=1);

namespace Larmias\Database\Providers;

use Larmias\Contracts\ConfigInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Manager;
use Larmias\Database\Model\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Framework\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(ManagerInterface::class, Manager::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/database.php' => $this->app->getConfigPath() . 'database.php',
        ]);

        $config = $this->container->get(ConfigInterface::class);

        /** @var ManagerInterface $manager */
        $manager = $this->container->make(ManagerInterface::class, ['config' => $config->get('database', [])]);
        $manager->setEventDispatcher($this->container->get(EventDispatcherInterface::class));

        Model::maker(function (Model $model) use ($manager) {
            $model->setManager($manager);
        });
    }
}