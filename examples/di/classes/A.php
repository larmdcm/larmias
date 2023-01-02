<?php

namespace Di;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Annotation\Inject;
use Larmias\HttpServer\Annotation\Middleware;

#[ClassAnnotation('A')]
#[Middleware]
#[Middleware]
class A extends B
{
    #[Inject]
    protected ContainerInterface $container;

    #[MethodAnnotation(__CLASS__)]
    public function index()
    {
        dump($this->container ?? null);
    }

    public function index2()
    {
        dump($this->config ?? null);
    }
}