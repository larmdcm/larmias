<?php

declare(strict_types=1);

namespace Larmias\Constants\Providers;

use Larmias\Constants\AbstractConstants;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\TranslatorInterface;

class ConstantsServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        if ($this->container->has(TranslatorInterface::class)) {
            AbstractConstants::setTranslator($this->container->get(TranslatorInterface::class));
        }
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}