<?php
declare(strict_types=1);

return [
    'driver' => \Larmias\View\Driver\Blade::class,
    'view_path' => dirname(__DIR__) . '/views',
    'tpl_cache' => true,
    'tpl_begin' => '{{',
    'tpl_end' => '}}',
    'tpl_raw_begin' => '{!!',
    'tpl_raw_end' => '!!}',
    'view_cache_path' => dirname(__DIR__) . '/runtime/temp',
    'view_suffix' => 'php',
];