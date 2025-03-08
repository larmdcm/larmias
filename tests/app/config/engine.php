<?php

declare(strict_types=1);

return [
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'settings' => [
        \Larmias\Engine\Constants::OPTION_EVENT_LOOP_CLASS => \Workerman\Events\Fiber::class,
        'settings' => [
            'pid_file' => dirname(__DIR__) . '/runtime/larmias.pid',
            'log_file' => dirname(__DIR__) . '/runtime/larmias.log',
        ],
    ],
];