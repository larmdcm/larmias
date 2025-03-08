<?php

declare(strict_types=1);

namespace Larmias\Coroutine;

use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Facade\AbstractFacade;

/**
 * @method static CoroutineCallableInterface create(callable $callable, ...$params)
 * @method static int id()
 * @method static int pid(?int $id = null)
 * @method static void set(array $config)
 * @method static void defer(callable $callable)
 * @method static void yield (mixed $value = null)
 * @method static void resume(int $id, mixed ...$params)
 */
class Coroutine extends AbstractFacade
{
    public static function getFacadeAccessor(): string|object
    {
        return CoroutineInterface::class;
    }
}