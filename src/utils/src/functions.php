<?php

use Larmias\Utils\Arr;
use Larmias\Utils\Collection;

if (!function_exists('throw_if')) {
    /**
     * 按条件抛异常
     *
     * @param mixed $condition
     * @param Throwable|string $exception
     * @param array ...$parameters
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
}

if (!function_exists('throw_unless')) {
    /**
     * 按条件抛异常
     *
     * @param mixed $condition
     * @param Throwable|string $exception
     * @param array ...$parameters
     * @return void
     * @throws Throwable
     */
    function throw_unless(mixed $condition, Throwable|string $exception, ...$parameters): void
    {
        if (!$condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }
    }
}

if (!function_exists('tap')) {
    /**
     * 对一个值调用给定的闭包，然后返回该值
     *
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function tap(mixed $value,callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return $value;
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('value')) {
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
}

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return Collection
     */
    function collect(mixed $value = null): Collection
    {
        return new Collection($value);
    }
}

if (!function_exists('data_fill')) {
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
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    function data_get(mixed $target,string|array $key, mixed $default = null): mixed
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
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(mixed &$target, string|array $key, mixed $value,bool $overwrite = true): mixed
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
}
