<?php

declare(strict_types=1);

namespace Larmias\Support;

use Larmias\Context\Context;
use RuntimeException;
use function method_exists;

class Helper
{
    /**
     * @var array
     */
    protected static array $cache = [];

    /**
     * 判断对象方法是否存在
     * @param object $object
     * @param string|array $methods
     * @return bool
     */
    public static function isMethodsExists(object $object, string|array $methods): bool
    {
        foreach ((array)$methods as $method) {
            if (!method_exists($object, $method)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $handler
     * @return array
     */
    public static function prepareHandler(string|array $handler): array
    {
        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            if (!isset($array[1]) && class_exists($handler) && method_exists($handler, '__invoke')) {
                $array[1] = '__invoke';
            }
            return [$array[0], $array[1] ?? null];
        }

        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }

        throw new RuntimeException('Handler not exist.');
    }

    /**
     * @return bool
     */
    public static function isTrueAsync(): bool
    {
        return Context::inCoroutine() && !Context::inFiber();
    }

    /**
     * 获取启动文件路径
     * @return string
     */
    public static function getStartFile(): string
    {
        if (!isset(static::$cache[__FUNCTION__])) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            static::$cache[__FUNCTION__] = end($backtrace)['file'];
        }
        return static::$cache[__FUNCTION__];
    }
}