<?php

declare(strict_types=1);

namespace Larmias\Di\Providers;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Di\Annotation;
use Larmias\Di\AnnotationManager;
use Larmias\Framework\ServiceProvider;

class AnnotationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        /** @var AnnotationInterface $annotation */
        $this->container->bindIf(AnnotationInterface::class, Annotation::class);
        $annotation = $this->container->make(AnnotationInterface::class, ['config' => $config->get('annotation', [])]);
        AnnotationManager::init($annotation);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/annotation.php' => $this->app->getConfigPath() . 'annotation.php',
        ]);
        AnnotationManager::scan();
    }
}