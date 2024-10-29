<?php

declare(strict_types=1);

namespace Larmias\Contracts\Aop;

interface AspectInterface
{
    public function process(JoinPointInterface $joinPoint);
}