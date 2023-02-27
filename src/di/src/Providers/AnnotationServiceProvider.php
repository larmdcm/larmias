<?php

declare(strict_types=1);

namespace Larmias\Di\Providers;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Di\Annotation;
use Larmias\Di\AnnotationManager;
use Larmias\Di\Contracts\AnnotationInterface;

class AnnotationServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {

    }

    /**
     * @return void
     */
    public function register(): void
    {
        /** @var AnnotationInterface $annotation */
        $this->container->bind(AnnotationInterface::class, Annotation::class);
        $annotation = $this->container->make(AnnotationInterface::class, ['config' => $this->config->get('annotation', [])]);
        AnnotationManager::init($annotation);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function boot(): void
    {
        AnnotationManager::scan();
    }
}