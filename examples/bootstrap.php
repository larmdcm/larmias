<?php

define('BASE_PATH',dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

if (!function_exists('println')) {
    /**
     * 换行打印输出
     *
     * @param string|Stringable|null $format
     * @param ...$args
     * @return void
     */
    function println(string|Stringable $format = null,...$args): void
    {
        printf($format . PHP_EOL,...$args);
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dump(...$vars)
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
    }
}