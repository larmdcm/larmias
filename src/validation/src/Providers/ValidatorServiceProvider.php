<?php

declare(strict_types=1);

namespace Larmias\Validation\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\TranslatorInterface;
use Larmias\Contracts\ValidatorInterface;
use Larmias\Validation\Validator;

class ValidatorServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        $this->container->bind(ValidatorInterface::class, Validator::class);
        Validator::maker(function (Validator $validator) {
            if ($this->container->has(TranslatorInterface::class)) {
                $validator->setTranslator($this->container->get(TranslatorInterface::class));
            }
        });
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}