<?php

declare(strict_types=1);

namespace Larmias\Utils;

use Larmias\Contracts\ContainerInterface;
use TypeError;

class ApplicationContext
{
    /**
     * @var ContainerInterface|null
     */
    protected static ?ContainerInterface $container = null;

    /**
     * @throws TypeError
     */
    public static function getContainer(): ContainerInterface
    {
        return static::$container;
    }

    /**
     * @param ContainerInterface $container
     * @return ContainerInterface
     */
    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        static::$container = $container;
        return static::$container;
    }

    /**
     * @return bool
     */
    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }
}