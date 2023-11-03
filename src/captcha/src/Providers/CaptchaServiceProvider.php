<?php

declare(strict_types=1);

namespace Larmias\Captcha\Providers;

use Larmias\Framework\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/captcha.php' => $this->app->getConfigPath() . 'captcha.php',
        ]);
    }
}