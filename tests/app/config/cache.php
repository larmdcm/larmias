<?php

declare(strict_types=1);

use Larmias\Codec\Packer\PhpSerializerPacker;
use function Larmias\Support\env;

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores' => [
        'file' => [
            'driver' => \Larmias\Cache\Driver\File::class,
            'packer' => PhpSerializerPacker::class,
            'expire' => 0,
            'path' => \Larmias\Framework\app()->getRuntimePath() . '/cache',
            'prefix' => '',
            'cache_sub_dir' => false,
            'hash_type' => 'md5',
        ],
        'redis' => [
            'driver' => \Larmias\Cache\Driver\Redis::class,
            'packer' => PhpSerializerPacker::class,
            'expire' => 0,
            'prefix' => '',
            'handler' => null
        ]
    ],
];