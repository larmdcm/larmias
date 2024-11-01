<?php

declare(strict_types=1);

namespace Larmias\Encryption\Providers;

use Larmias\Framework\ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/encryption.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'encryption.php',
        ]);
    }
}