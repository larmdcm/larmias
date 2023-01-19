<?php

declare(strict_types=1);

namespace Larmias\Utils;

use Larmias\Contracts\ContainerInterface;
use TypeError;

class ApplicationContext
{
    protected static ?ContainerInterface $container = null;

    /**
     * @throws TypeError
     */
    public static function getContainer(): ContainerInterface
    {
        return static::$container;
    }

    public static function setContainer(ContainerInterface $container): void
    {
        static::$container = $container;
    }

    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }
}