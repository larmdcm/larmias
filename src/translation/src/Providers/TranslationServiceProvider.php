<?php

declare(strict_types=1);

namespace Larmias\Translation\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\TranslatorInterface;
use Larmias\Translation\Translator;

class TranslationServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        $this->container->bind(TranslatorInterface::class, Translator::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}