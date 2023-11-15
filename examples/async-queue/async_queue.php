<?php

declare(strict_types=1);

return [
    'default' => 'redis',
    'queues' => [
        'redis' => [
            'driver' => \Larmias\AsyncQueue\Driver\Redis::class,
            // redis name
            'redis_name' => 'default',
            // 键前缀
            'prefix' => 'queues:',
            // 队列名称
            'name' => 'default',
            // 任务处理超时时间
            'handle_timeout' => 10,
            // 等待时间（秒）
            'wait_time' => 1,
            // 消费间隔（秒）
            'timespan' => 1,
        ]
    ]
];