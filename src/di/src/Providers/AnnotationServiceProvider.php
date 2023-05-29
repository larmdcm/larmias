<?php

declare(strict_types=1);

namespace Larmias\Di\Providers;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\Di\Annotation;
use Larmias\Di\AnnotationManager;

class AnnotationServiceProvider implements ServiceProviderInterface
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
        /** @var AnnotationInterface $annotation */
        $this->container->bindIf(AnnotationInterface::class, Annotation::class);
        $annotation = $this->container->make(AnnotationInterface::class, ['config' => $this->config->get('annotation', [])]);
        AnnotationManager::init($annotation);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/annotation.php' => $app->getConfigPath() . 'annotation.php',
            ]);
        }
        AnnotationManager::scan();
    }
}