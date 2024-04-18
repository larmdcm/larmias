<?php

declare(strict_types=1);

namespace Larmias\Collection;

use Larmias\Contracts\CollectionInterface;
use Closure;

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
 * @param string|array|null $key
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
 * Get the first element of an array. Useful for method chaining.
 *
 * @param array $array
 * @return mixed
 */
function head(array $array): mixed
{
    return reset($array);
}

/**
 * Get the last element from an array.
 *
 * @param array $array
 * @return mixed
 */
function last(array $array): mixed
{
    return end($array);
}