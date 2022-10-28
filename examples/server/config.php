<?php

return [
    'engine'  => \Larmias\Server\ServerEngine::ENGINE_WORKERS,
    'servers' => [
        [
            'name'      => 'http',
            'type'      => \Larmias\Server\ServerType::SERVER_HTTP,
            'handler'   => \Larmias\HttpServer\Server::class,
            'host'      => '0.0.0.0',
            'port'      => 9501,
        ]
    ]
];