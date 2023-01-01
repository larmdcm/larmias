<?php

namespace Di;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Annotation\Inject;

#[ClassAnnotation('A')]
class A
{
    #[Inject]
    protected ContainerInterface $container;

    #[MethodAnnotation(__CLASS__)]
    public function index()
    {
        dump($this->container ?? null);
    }
}