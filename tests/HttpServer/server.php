<?php

/** @var ApplicationInterface $app */

use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Timer;
use Larmias\Engine\WorkerType;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\HttpServer\Handler\SseHandler;
use Larmias\HttpServer\Routing\Router;
use Larmias\HttpServer\Server as HttpServer;
use Larmias\Contracts\Http\OnRequestInterface;

$app = require __DIR__ . '/../app.php';

$kernel = new Kernel($app->getContainer());

$kernel->setConfig(EngineConfig::build(config: [
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9601,
            'settings' => [
                'worker_num' => 1,
                'open_heartbeat_check' => true,
                'heartbeat_check_interval' => 1,
                'heartbeat_idle_time' => 3,
                'heartbeat_ignore_processing' => true,
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, OnRequestInterface::ON_REQUEST],
            ]
        ],
    ],
    'settings' => [
        // \Larmias\Engine\Constants::OPTION_EVENT_LOOP_CLASS => \Larmias\Engine\WorkerMan\EventDriver\Select::class,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () {

            Router::get('/', function (ResponseInterface $resp) {
                return $resp->raw('hello,world!');
            });

            Router::get('/chunk', function (ResponseInterface $resp) {
                $resp->write('hello1<br/>');
                $resp->write('hello2<br/>');
                $resp->write('hello3<br/>');
                return $resp;
            });

            Router::get('/sleep', function (RequestInterface $request) {
                $sleepTime = $request->input('time', 3);
                if ($sleepTime > 0) {
                    sleep($sleepTime);
                }
                return 'success';
            });

            Router::get('/sseView', function (RequestInterface $request, ResponseInterface $resp) {
                return $resp->html(file_get_contents(__DIR__ . '/sse.html'));
            });

            Router::get('/sse', function (RequestInterface $request, ResponseInterface $resp) {
                return $resp->sse(function (SseHandler $sseHandler) {
                    for ($i = 1; $i <= 3; $i++) {
                        $sseHandler->write(\Larmias\HttpServer\Message\ServerSentEvents::make(['data' => 'hello' . $i]));
                        Timer::sleep(1);
                    }
                    $sseHandler->end();
                });
            });
        }
    ],
]));

$kernel->run();