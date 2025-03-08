<?php

declare(strict_types=1);

namespace Larmias\Database\Providers;

use Larmias\Contracts\ConfigInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Manager;
use Larmias\Database\Model;
use Larmias\Framework\ServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

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
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/database.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'database.php',
        ]);

        $config = $this->container->get(ConfigInterface::class);

        /** @var ManagerInterface $manager */
        $manager = $this->container->make(ManagerInterface::class, ['config' => $config->get('database', [])]);
        $manager->setEventDispatcher($this->container->get(EventDispatcherInterface::class));

        Model::maker(function (Model $model) use ($manager) {
            $model->setManager($manager);
            $schemaInfo = $model->getConnection()->getSchemaInfo($model->getTable());
            $model->setSchema(array_merge($schemaInfo['type'], $model->getSchema()));
        });
    }
}