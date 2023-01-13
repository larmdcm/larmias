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

    public function index3()
    {
        /** @var C $c1 */
        $c1 = $this->container->get(C::class);
        /** @var C $c1 */
        $c2 = $this->container->get(C::class);
        dump($c1 === $c2);
        dump($c1->validator === $c2->validator);
    }

    public function invoke()
    {
        println($this->container->invoke([$this, 'invokeTest'], ['name' => __FUNCTION__]));
    }

    public function invokeTest(string $name)
    {
        return $name;
    }
}