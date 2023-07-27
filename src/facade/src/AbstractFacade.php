<?php

declare(strict_types=1);

namespace Larmias\Facade;

use Psr\Container\ContainerInterface;

abstract class AbstractFacade
{
    public static ContainerInterface $container;

    /**
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}