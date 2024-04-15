<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    // debug模式
    'debug' => env('APP_DEBUG', false),
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // providers
    'providers' => [],
];