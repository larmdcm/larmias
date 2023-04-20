<?php

declare(strict_types=1);

return [
    'driver' => \Larmias\Lock\Drivers\Redis::class,
    'prefix' => '',
    'expire' => 30000,
    'wait_sleep_time' => 30,
    'wait_timeout' => 10000,
];