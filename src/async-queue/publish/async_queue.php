<?php

declare(strict_types=1);

return [
    'default' => 'redisStream',
    'queues' => [
        'redisStream' => [
            'driver' => \Larmias\AsyncQueue\Driver\RedisStream::class,
            // redis name
            'redis_name' => 'default',
            // 键前缀
            'prefix' => 'queue:',
            // 队列名称
            'name' => 'default',
            // 超时时间
            'timeout' => 0,
        ]
    ]
];