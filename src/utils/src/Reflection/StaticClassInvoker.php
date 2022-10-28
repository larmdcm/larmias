<?php

declare(strict_types=1);

namespace Larmias\Utils\Reflection;

use ReflectionMethod;

class StaticClassInvoker
{
    /**
     * @param string|array $method
     * @param array $args
     * @param bool $accessible
     * @return mixed
     * @throws \ReflectionException
     */
    public static function invoke(string|array $method,array $args = [],bool $accessible = false): mixed
    {
        if (is_array($method)) {
            [$class,$method] = $method;
        } else {
            [$class, $method] = explode('::', $method);
        }
        $reflect = new ReflectionMethod($class, $method);
        $paramsBind = new ParameterBind($reflect);
        if ($accessible) {
            $reflect->setAccessible($accessible);
        }
        return $reflect->invokeArgs(null, $paramsBind($args));
    }
}