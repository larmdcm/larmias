<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Providers;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\ExceptionHandler\Annotation\ExceptionHandler;
use Larmias\ExceptionHandler\Annotation\Handler\ExceptionHandlerAnnotationHandler;
use Larmias\ExceptionHandler\Contracts\ExceptionHandlerDispatcherInterface;
use Larmias\ExceptionHandler\ExceptionHandlerDispatcher;
use Larmias\Framework\ServiceProvider;
use Throwable;

class ExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws Throwable
     */
    public function register(): void
    {
        $this->container->bindIf(ExceptionHandlerDispatcherInterface::class, ExceptionHandlerDispatcher::class);
        if ($this->container->has(AnnotationInterface::class)) {
            /** @var AnnotationInterface $annotation */
            $annotation = $this->container->get(AnnotationInterface::class);
            $annotation->addHandler(ExceptionHandler::class, ExceptionHandlerAnnotationHandler::class);
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/exceptions.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'exceptions.php',
        ]);
    }
}