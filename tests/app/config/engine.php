<?php

declare(strict_types=1);

return [
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'settings' => [
        \Larmias\Engine\Constants::OPTION_EVENT_LOOP_CLASS => \Workerman\Events\Fiber::class,
    ],
];