<?php

declare(strict_types=1);

namespace Larmias\Framework\Providers;

use Larmias\Di\AnnotationManager;
use Larmias\Di\Contracts\AnnotationInterface;
use Larmias\Framework\ServiceProvider;
use function Larmias\Framework\config;

class AnnotationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        /** @var AnnotationInterface $annotation */
        $annotation = $this->app->make(AnnotationInterface::class, ['config' => config('annotation', [])]);
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