<?php

return [
    'default' => 'redis',
    'stores' => [
        'file' => [
            'driver' => \Larmias\Cache\Driver\File::class,
            'packer' => \Larmias\Utils\Packer\FrameSerializer::class,
        ],
        'redis' => [
            'driver' => \Larmias\Cache\Driver\Redis::class,
            'packer' => \Larmias\Utils\Packer\FrameSerializer::class,
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'select' => 0,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => false,
            'prefix' => '',
            'handler' => null
        ]
    ],
];