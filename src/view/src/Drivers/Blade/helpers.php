<?php

declare(strict_types=1);

namespace Larmias\View\Drivers\Blade;

/**
 * Escape HTML entities in a string.
 *
 * @param string $value
 * @return string
 */
function e(string $value): string
{
    return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
}

/**
 * Get all of the given array except for a specified array of items.
 *
 * @param array $array
 * @param array|string $keys
 * @return array
 */
function array_except(array $array, array|string $keys): array
{
    foreach ((array)$keys as $key) {
        unset($array[$key]);
    }

    return $array;
}
