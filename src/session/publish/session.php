<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'default' => env('SESSION_HANDLER', 'file'),
    'name' => 'PHPSESSID',
    'cookie_lifetime' => 0,
    'packer' => \Larmias\Codec\Packer\PhpSerializerPacker::class,
    'handlers' => [
        'file' => [
            'handler' => \Larmias\Session\Handler\FileHandler::class,
            'path' => null,
            'prefix' => 'larmias_',
            'expire' => 86400,
            'data_compress' => false,
            'gc_probability' => 1,
            'gc_divisor' => 100,
        ],
        'redis' => [
            'handler' => \Larmias\Session\Handler\RedisHandler::class,
            'prefix' => 'larmias_',
            'expire' => 86400,
        ]
    ]
];