<?php

declare(strict_types=1);

namespace Larmias\Context;

use Larmias\Contracts\ContextInterface;
use RuntimeException;

/**
 * @method static mixed get(string $id, mixed $default = null)
 * @method static mixed set(string $id, mixed $value)
 * @method static mixed remember(string $id, \Closure $closure)
 * @method static bool has(string $id)
 * @method static void destroy(string $id)
 */
class Context
{
    /**
     * @var ContextInterface|null
     */
    protected static ?ContextInterface $context = null;

    /**
     * @param ContextInterface $context
     * @return void
     */
    public static function setContext(ContextInterface $context): void
    {
        static::$context = $context;
    }

    /**
     * @return ContextInterface
     */
    public static function getContext(): ContextInterface
    {
        return static::$context;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (static::$context === null) {
            throw new RuntimeException("not support: Context");
        }

        return call_user_func_array([static::$context, $name], $arguments);
    }
}