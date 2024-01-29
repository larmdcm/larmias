<?php

declare(strict_types=1);

namespace Larmias\Di\AnnotationHandler;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Invoker\InvokeResolver;

class InvokeResolverAnnotationHandler implements AnnotationHandlerInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param array $param
     * @return void
     */
    public function collect(array $param): void
    {
        InvokeResolver::add($this->container->make($param['class']));
    }

    /**
     * @return void
     */
    public function handle(): void
    {
    }
}