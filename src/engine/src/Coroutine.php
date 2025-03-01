<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Facade\AbstractFacade;
use ArrayObject;

/**
 * @method static CoroutineCallableInterface create(callable $callable, ...$params)
 * @method static int id()
 * @method static int pid(?int $id = null)
 * @method static void set(array $config)
 * @method static void defer(callable $callable)
 * @method static ArrayObject|null getContextFor(?int $id = null)
 * @method static void yield ()
 * @method static void resume(int $id)
 */
class Coroutine extends AbstractFacade
{
    public static function getFacadeAccessor(): string|object
    {
        return CoroutineInterface::class;
    }
}