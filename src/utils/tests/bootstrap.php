<?php

define('BASE_PATH',dirname(__DIR__));

if (!function_exists('println')) {
    /**
     * 换行打印输出
     *
     * @param string|Stringable $format
     * @param ...$args
     * @return void
     */
    function println(string|Stringable $format = null,...$args): void
    {
        printf($format . PHP_EOL,...$args);
    }
}
