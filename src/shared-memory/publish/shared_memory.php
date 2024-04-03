<?php

declare(strict_types=1);

use Larmias\Codec\Protocol\FrameProtocol;

return [
    'client' => [
        'host' => '127.0.0.1',
        'port' => 2000,
        'timeout' => 3,
        'event' => [],
        'async' => false,
        'ping_interval' => 30000,
        'auto_connect' => true,
        'break_reconnect' => true,
        'password' => '',
        'select' => 'default',
        'protocol' => FrameProtocol::class,
    ]
];