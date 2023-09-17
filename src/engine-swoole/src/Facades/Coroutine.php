<?php

namespace Larmias\Engine\Swoole\Facades;

use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Facade\AbstractFacade;

class Coroutine extends AbstractFacade
{
    /**
     * @return string|object
     */
    public static function getFacadeAccessor(): string|object
    {
        return CoroutineInterface::class;
    }
}