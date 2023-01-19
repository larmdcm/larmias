<?php

namespace Larmias\View\Blade;

class Autoloader
{
    /**
     * Registers Larmias\View\Blade\Autoloader as an SPL autoloader and require helpers function.
     */
    public static function register()
    {
        require '../src/helpers.php';

        spl_autoload_register([self::class, 'autoload']);
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     */
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'Larmias\View\Blade')) {
            return;
        }
        if (is_file($file = dirname(__FILE__). '/' . str_replace('\\', '/', substr($class, 13)) . '.php')) {
            require $file;
        }
    }
}
