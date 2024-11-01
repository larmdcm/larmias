<?php

declare(strict_types=1);

namespace Larmias\Translation\Providers;

use Larmias\Contracts\TranslatorInterface;
use Larmias\Translation\Translator;
use Larmias\Framework\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(TranslatorInterface::class, Translator::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/translation.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'translation.php',
        ]);
    }
}