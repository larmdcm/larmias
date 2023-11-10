<?php

declare(strict_types=1);

return [
    'driver' => Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => \Larmias\Engine\WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9501,
            'settings' => [
                'worker_num' => \Larmias\Support\get_cpu_num(),
            ],
            'callbacks' => [
                \Larmias\Engine\Event::ON_REQUEST => [Larmias\HttpServer\Server::class, \Larmias\Contracts\Http\OnRequestInterface::ON_REQUEST]
            ]
        ]
    ],
    'settings' => [],
    'callbacks' => []
];