<?php

declare(strict_types=1);

return [
    'default' => 'file',
    'stores' => [
        'file' => [
            'driver' => \Larmias\Cache\Driver\File::class,
            'packer' => \Larmias\Utils\Packer\PhpSerializerPacker::class,
            'expire' => 0,
            'prefix' => '',
        ],
        'redis' => [
            'driver' => \Larmias\Cache\Driver\Redis::class,
            'packer' => \Larmias\Utils\Packer\PhpSerializerPacker::class,
            'expire' => 0,
            'prefix' => '',
            'handler' => null
        ]
    ],
];