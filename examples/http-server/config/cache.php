<?php

return [
    'default' => 'redis',
    'stores' => [
        'file' => [
            'driver' => \Larmias\Cache\Driver\File::class,
            'packer' => \Larmias\Support\Packer\PhpSerializerPacker::class,
            'expire' => 0,
            'prefix' => '',
        ],
        'redis' => [
            'driver' => \Larmias\Cache\Driver\Redis::class,
            'packer' => \Larmias\Support\Packer\PhpSerializerPacker::class,
            'expire' => 0,
            'prefix' => '',
            'handler' => null
        ]
    ],
];