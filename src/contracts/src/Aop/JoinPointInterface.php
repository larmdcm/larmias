<?php

declare(strict_types=1);

namespace Larmias\Contracts\Aop;

interface JoinPointInterface
{
    public function process();
}