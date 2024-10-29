<?php

declare(strict_types=1);

namespace Larmias\Di\Aop;

use Closure;
use Larmias\Contracts\Aop\JoinPointInterface;

class JoinPoint implements JoinPointInterface
{
    /**
     * @param Closure $handler
     * @param mixed $param
     */
    public function __construct(protected Closure $handler, protected mixed $param)
    {
    }

    /**
     * @return mixed
     */
    public function process(): mixed
    {
        return call_user_func($this->handler, $this->param);
    }
}