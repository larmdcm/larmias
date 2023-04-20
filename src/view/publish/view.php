<?php

declare(strict_types=1);

return [
    'driver' => \Larmias\View\Drivers\Blade::class,
    'view_path' => null,
    'tpl_cache' => true,
    'tpl_begin' => '{{',
    'tpl_end' => '}}',
    'tpl_raw_begin' => '{!!',
    'tpl_raw_end' => '!!}',
    'view_cache_path' => null,
    'view_suffix' => 'php',
];