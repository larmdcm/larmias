<?php

declare(strict_types=1);

namespace Larmias\FileSystem\Providers;

use Larmias\Framework\ServiceProvider;
use Throwable;

class FileSystemServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/filesystem.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'filesystem.php',
        ]);
    }
}