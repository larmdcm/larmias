<?php

declare(strict_types=1);

return [
    'default' => 'redisStream',
    'queues' => [
        'redisStream' => [
            'driver' => \Larmias\AsyncQueue\Drivers\RedisStream::class,
            // redis name
            'redis_name' => 'queue',
            // 键前缀
            'prefix' => 'queue:',
            // 队列名称
            'name' => 'default',
            // 超时时间
            'timeout' => 0,
            // 队列最大长度
            'maxlength' => 0,
            // 队列最大长度近似模式
            'approximate' => false,
            // 分组
            'group' => 'streamGroup',
            // 消费者
            'consumer' => 'streamConsumer',
            // 失败队列消费者
            'fal_consumer' => 'streamFailConsumer',
        ]
    ]
];