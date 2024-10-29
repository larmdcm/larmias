<?php

declare(strict_types=1);

namespace Larmias\Support;

use Composer\Autoload\ClassLoader;

use RuntimeException;

class Composer
{
    /**
     * @return ClassLoader
     */
    public static function getClassLoader(): ClassLoader
    {
        foreach (spl_autoload_functions() as $autoloadFunction) {
            if (is_array($autoloadFunction) && ($loader = $autoloadFunction[0]) instanceof ClassLoader) {
                return $loader;
            }
        }
        throw new RuntimeException('Cannot find any composer class loader');
    }
}