<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'driver' => \Larmias\Lock\Driver\Redis::class,
    'prefix' => env('LOCK_PREFIX', 'larmias_'),
    'expire' => 30000,
    'wait_sleep_time' => 30,
    'wait_timeout' => 10000,
];