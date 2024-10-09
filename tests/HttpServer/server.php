<?php

/** @var ApplicationInterface $app */

use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\WorkerType;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\HttpServer\Routing\Router;
use Larmias\HttpServer\Server as HttpServer;
use Larmias\Contracts\Http\OnRequestInterface;

$app = require __DIR__ . '/../app.php';

$kernel = new Kernel($app->getContainer());

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9601,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, OnRequestInterface::ON_REQUEST],
            ]
        ],
    ],
    'settings' => [
        \Larmias\Engine\Constants::OPTION_EVENT_LOOP_CLASS => \Larmias\Engine\WorkerMan\EventDriver\Select::class,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () {
            Router::get('/', function (RequestInterface $request, ResponseInterface $resp) {
                return $resp->html(file_get_contents(__DIR__ . '/sse.html'));
            });
            Router::get('/sse', function (RequestInterface $request, ResponseInterface $resp) {
                $resp = $resp->withHeader('content-type', 'text/event-stream');
                $resp->write('data:' . json_encode(['message' => 'hello', 'time' => time()]));
                return $resp;
            });
        }
    ],
]));

$kernel->run();