<?php

return [
    'default' => 'stdout',
    'realtime_write' => true,
    'level_channels' => [],
    'level' => [],
    'channels' => [
        'stdout' => [
            'handler' => 'stdout',
            'formatter' => 'default',
        ]
    ],
    'handlers' => [
        'stdout' => [
            'class' => \Larmias\Log\Handler\StdoutHandler::class,
            'constructor' => []
        ]
    ],
    'formatters' => [
        'default' => [
            'class' => \Larmias\Log\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
            ]
        ]
    ]
];