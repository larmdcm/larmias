<?php

declare(strict_types=1);

namespace LarmiasTest\Di\Classes;

use Larmias\Contracts\Aop\AspectInterface;
use Larmias\Contracts\Aop\JoinPointInterface;
use Larmias\Di\Annotation\Invoke;
use LarmiasTest\Di\Annotation\Invoker;

#[Invoke(annotations: [Invoker::class])]
class UserInvoker implements AspectInterface
{
    public function process(JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->process();
        $result['id'] = 2;
        return $result;
    }
}