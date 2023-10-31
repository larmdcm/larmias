<?php
declare(strict_types=1);

return [
    'default' => 'redis',
    'name' => 'PHPSESSID',
    'cookie_lifetime' => 60,
    'packer' => \Larmias\Support\Packer\PhpSerializerPacker::class,
    'handlers' => [
        'file' => [
            'handler' => \Larmias\Session\Handler\FileHandler::class,
            'path' => dirname(__DIR__) . '/session',
            'prefix' => '',
            'expire' => 1440,
            'data_compress' => false,
            'gc_probability' => 1,
            'gc_divisor' => 100,
        ],
        'redis' => [
            'handler' => \Larmias\Session\Handler\RedisHandler::class,
            'prefix' => '',
            'expire' => 1440,
        ]
    ]
];