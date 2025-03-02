<?php

declare(strict_types=1);

namespace Larmias\Di\Providers;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\Aop\AopInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\Di\ClassScannerInterface;
use Larmias\Di\Annotation;
use Larmias\Di\Annotation\Aspect;
use Larmias\Di\Annotation\Dependence;
use Larmias\Di\Annotation\Handler\AspectAnnotationHandler;
use Larmias\Di\Annotation\Handler\DependenceAnnotationHandler;
use Larmias\Di\Annotation\Handler\InjectAnnotationHandler;
use Larmias\Di\Annotation\Handler\InvokeResolverAnnotationHandler;
use Larmias\Di\Annotation\Inject;
use Larmias\Di\Annotation\Invoke;
use Larmias\Di\Aop;
use Larmias\Di\ClassScanner;
use Larmias\Framework\ServiceProvider;
use Throwable;

class DiServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws Throwable
     */
    public function register(): void
    {
        $this->container->bindIf([
            AnnotationInterface::class => Annotation::class,
            AopInterface::class => Aop::class,
            ClassScannerInterface::class => ClassScanner::class,
        ]);
        /** @var AnnotationInterface $annotation */
        $annotation = $this->container->make(AnnotationInterface::class);
        $annotation->addHandler(Inject::class, InjectAnnotationHandler::class);
        $annotation->addHandler(Invoke::class, InvokeResolverAnnotationHandler::class);
        $annotation->addHandler(Aspect::class, AspectAnnotationHandler::class);
        $annotation->addHandler(Dependence::class, DependenceAnnotationHandler::class);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/aop.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'aop.php',
        ]);

        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);
        $aopConfig = $config->get('aop', []);
        /** @var ClassScannerInterface $classScanner */
        $classScanner = $this->container->make(ClassScannerInterface::class, ['config' => $aopConfig]);
        $classScanner->scan();
    }
}