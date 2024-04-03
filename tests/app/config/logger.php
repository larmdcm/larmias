<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'default' => env('LOG_DEFAULT', 'stdout'),
    'realtime_write' => true,
    'level_channels' => [],
    'level' => [],
    'channels' => [
        'stdout' => [
            'handler' => 'stdout',
            'formatter' => 'default',
        ],
        'file' => [
            'handler' => 'file',
            'formatter' => 'default',
        ]
    ],
    'handlers' => [
        'stdout' => [
            'handler' => \Larmias\Log\Handler\StdoutHandler::class,
        ],
        'file' => [
            'handler' => \Larmias\Log\Handler\FileHandler::class,
        ],
    ],
    'formatters' => [
        'default' => [
            'handler' => \Larmias\Log\Formatter\LineFormatter::class,
        ]
    ]
];