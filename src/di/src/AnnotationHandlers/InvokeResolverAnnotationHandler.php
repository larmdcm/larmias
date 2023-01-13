<?php

declare(strict_types=1);

namespace Larmias\Di\AnnotationHandlers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Contracts\AnnotationHandlerInterface;
use Larmias\Di\Invoker\InvokeResolver;

class InvokeResolverAnnotationHandler implements AnnotationHandlerInterface
{
    public function __construct(protected ContainerInterface $container)
    {

    }

    public function collect(array $param): void
    {
        InvokeResolver::add($this->container->make($param['class']));
    }

    public function handle(): void
    {
    }
}