<?php

declare(strict_types=1);

namespace Larmias\Utils;

use Larmias\Contracts\CollectionInterface;
use Throwable;
use Closure;
use Stringable;

/**
 * 换行打印输出
 *
 * @param string|Stringable|null $format
 * @param ...$args
 * @return void
 */
function println(string|Stringable $format = null, ...$args): void
{
    printf($format . PHP_EOL, ...$args);
}

/**
 * 格式化异常信息
 *
 * @param \Throwable $e
 * @param bool $trace
 * @return string
 */
function format_exception(Throwable $e, bool $trace = true): string
{
    $message = $e->getFile() . '(' . $e->getLine() . ')' . ':' . $e->getMessage();
    if ($trace) {
        $message = $message . PHP_EOL . $e->getTraceAsString();
    }
    return $message;
}

/**
 * 判断值是否为空
 *
 * @param mixed $value
 * @return boolean
 */
function is_empty(mixed $value): bool
{
    return empty($value) && !is_numeric($value);
}


/**
 * 按条件抛异常
 *
 * @param mixed $condition
 * @param Throwable|string $exception
 * @param  ...$parameters
 * @return void
 *
 * @throws Throwable
 */
function throw_if(mixed $condition, Throwable|string $exception, ...$parameters): void
{
    if ($condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }
}

/**
 * 按条件抛异常
 *
 * @param mixed $condition
 * @param Throwable|string $exception
 * @param  ...$parameters
 * @return void
 * @throws Throwable
 */
function throw_unless(mixed $condition, Throwable|string $exception, ...$parameters): void
{
    if (!$condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }
}

/**
 * 对一个值调用给定的闭包，然后返回该值
 *
 * @param mixed $value
 * @param callable|null $callback
 * @return mixed
 */
function tap(mixed $value, callable $callback = null): mixed
{
    if (is_null($callback)) {
        return $value;
    }

    $callback($value);

    return $value;
}


/**
 * Return the default value of the given value.
 *
 * @param mixed $value
 * @return mixed
 */
function value(mixed $value): mixed
{
    return $value instanceof Closure ? $value() : $value;
}

/**
 * Create a collection from the given value.
 *
 * @param mixed $value
 * @return CollectionInterface
 */
function collect(mixed $value = null): CollectionInterface
{
    return new Collection($value);
}

/**
 * Fill in data where it's missing.
 *
 * @param mixed $target
 * @param string|array $key
 * @param mixed $value
 * @return mixed
 */
function data_fill(mixed &$target, string|array $key, mixed $value): mixed
{
    return data_set($target, $key, $value, false);
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param mixed $target
 * @param string|array $key
 * @param mixed $default
 * @return mixed
 */
function data_get(mixed $target, string|array|null $key, mixed $default = null): mixed
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    while (!is_null($segment = array_shift($key))) {
        if ('*' === $segment) {
            if ($target instanceof Collection) {
                $target = $target->all();
            } elseif (!is_array($target)) {
                return value($default);
            }

            $result = [];

            foreach ($target as $item) {
                $result[] = data_get($item, $key);
            }

            return in_array('*', $key) ? Arr::collapse($result) : $result;
        }

        if (Arr::accessible($target) && Arr::exists($target, $segment)) {
            $target = $target[$segment];
        } elseif (is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return value($default);
        }
    }

    return $target;
}

/**
 * Set an item on an array or object using dot notation.
 *
 * @param mixed $target
 * @param string|array $key
 * @param mixed $value
 * @param bool $overwrite
 * @return mixed
 */
function data_set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true): mixed
{
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {
        if (!Arr::accessible($target)) {
            $target = [];
        }

        if ($segments) {
            foreach ($target as &$inner) {
                data_set($inner, $segments, $value, $overwrite);
            }
        } elseif ($overwrite) {
            foreach ($target as &$inner) {
                $inner = $value;
            }
        }
    } elseif (Arr::accessible($target)) {
        if ($segments) {
            if (!Arr::exists($target, $segment)) {
                $target[$segment] = [];
            }

            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || !Arr::exists($target, $segment)) {
            $target[$segment] = $value;
        }
    } elseif (is_object($target)) {
        if ($segments) {
            if (!isset($target->{$segment})) {
                $target->{$segment} = [];
            }

            data_set($target->{$segment}, $segments, $value, $overwrite);
        } elseif ($overwrite || !isset($target->{$segment})) {
            $target->{$segment} = $value;
        }
    } else {
        $target = [];

        if ($segments) {
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}

/**
 * 获取类名(不包含命名空间)
 *
 * @param mixed $class
 * @return string
 */
function class_basename(mixed $class): string
{
    $class = is_object($class) ? get_class($class) : $class;
    return basename(str_replace('\\', '/', $class));
}