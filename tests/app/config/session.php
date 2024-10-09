<?php

declare(strict_types=1);

use Larmias\Session\Handler\FileHandler;
use Larmias\Session\Handler\RedisHandler;
use Larmias\Codec\Packer\PhpSerializerPacker;

return [
    'default' => 'file',
    'name' => 'PHPSESSID',
    'cookie_lifetime' => 0,
    'packer' => PhpSerializerPacker::class,
    'handlers' => [
        'file' => [
            'handler' => FileHandler::class,
            'path' => dirname(__DIR__) . '/runtime/session',
            'prefix' => 'larmias_',
            'expire' => 86400,
            'data_compress' => false,
            'gc_probability' => 1,
            'gc_divisor' => 100,
        ],
        'redis' => [
            'handler' => RedisHandler::class,
            'prefix' => 'larmias_',
            'expire' => 86400,
        ]
    ]
];