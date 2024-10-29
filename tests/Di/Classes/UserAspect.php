<?php

declare(strict_types=1);

namespace LarmiasTest\Di\Classes;

use Larmias\Contracts\Aop\AspectInterface;
use Larmias\Contracts\Aop\JoinPointInterface;
use Larmias\Di\Annotation\Aspect;
use LarmiasTest\Di\Annotation\Method;

#[Aspect([
    User::class,
], [
    Method::class,
])]
class UserAspect implements AspectInterface
{
    public function process(JoinPointInterface $joinPoint)
    {
        return $joinPoint->process();
    }
}