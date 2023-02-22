<?php

return [
    'default' => 'socketLog',
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
        ],
        'socketLog' => [
            'handler' => 'socketLog',
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
        'socketLog' => [
            'handler' => \Larmias\Log\Handler\SocketLogHandler::class,
            // socket服务器地址
            'host' => '192.168.33.99',
            // 发送的端口
            'port' => 1116,
            // 是否显示加载的文件列表
            'show_included_files' => false,
            // 日志强制记录到配置的client_id
            'force_client_ids' => ['pumpkin'],
            // 限制允许读取日志的client_id
            'allow_client_ids' => ['pumpkin'],
            //输出到浏览器默认展开的日志级别
            'expand_level' => ['debug'],
        ]
    ],
    'formatters' => [
        'default' => [
            'handler' => \Larmias\Log\Formatter\LineFormatter::class,
        ]
    ]
];