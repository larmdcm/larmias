<?php

declare(strict_types=1);

namespace Larmias\Utils;

class Helper
{
    /**
     * 判断对象方法是否存在
     *
     * @param object $object
     * @param string|array $methods
     * @return bool
     */
    public static function isMethodsExists(object $object, string|array $methods): bool
    {
        foreach ((array)$methods as $method) {
            if (!\method_exists($object, $method)) {
                return false;
            }
        }
        return true;
    }
}