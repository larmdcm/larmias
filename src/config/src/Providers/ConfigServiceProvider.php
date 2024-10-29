<?php

declare(strict_types=1);

namespace Larmias\Config\Providers;

use Larmias\Config\Annotation\Handler\ValueAnnotationHandler;
use Larmias\Config\Annotation\Value;
use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        if ($this->container->has(AnnotationInterface::class)) {
            /** @var AnnotationInterface $annotation */
            $annotation = $this->container->get(AnnotationInterface::class);
            $annotation->addHandler([
                Value::class,
            ], ValueAnnotationHandler::class);
        }
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}