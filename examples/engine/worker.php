<?php

use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;

return [
    'driver' => \Larmias\Engine\Drivers\WorkerS::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9863,
            'settings' => [
                'worker_num' => 1,
                'task_worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => function ($request,$response) {
                    $response->end('hello,world!');
                },
                Event::ON_WORKER_START => function () {
                    var_dump(1);
                }
            ]
        ]
    ],
    'settings'  => [],
    'callbacks' => [],
];