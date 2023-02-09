<?php

use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\HttpServer\Server as HttpServer;
use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\HttpServer\Routing\Router;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;
use Larmias\SharedMemory\Server as SharedMemoryServer;

return [
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9863,
            'settings' => [
                'worker_num' => 2,
                'task_worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, OnRequestInterface::ON_REQUEST]
            ]
        ],
        [
            'name' => 'watcherProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
                'watch' => [
                    'enabled' => true,
                    'includes' => [
                        __DIR__ . '/config',
                        __DIR__ . '/router.php',
                    ],
                ],
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [\Larmias\Engine\Process\WorkerHotUpdateProcess::class, 'handle'],
            ]
        ],
        [
            'name' => 'SharedMemory',
            'type' => WorkerType::TCP_SERVER,
            'host' => '0.0.0.0',
            'port' => 2000,
            'settings' => [
                'worker_num' => 1,
                'protocol' => \Workerman\Protocols\Frame::class,
                'auth_password' => '123456',
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [SharedMemoryServer::class, 'onWorkerStart'],
                Event::ON_CONNECT => [SharedMemoryServer::class, 'onConnect'],
                Event::ON_RECEIVE => [SharedMemoryServer::class, 'onReceive'],
                Event::ON_CLOSE => [SharedMemoryServer::class, 'onClose'],
            ]
        ],
        [
            'name' => 'taskProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [\Larmias\Task\Process\TaskProcess::class, 'handle'],
            ]
        ],
        [
            'name' => 'crontabProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [\Larmias\Crontab\Process\CrontabProcess::class, 'handle'],
            ]
        ]
    ],
    'settings' => [
        'eventLoop' => \Workerman\Events\Swoole::class,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => function (\Larmias\Engine\Contracts\WorkerInterface $worker) {
            $container = require '../di/container.php';
            $container->bind(ConfigInterface::class, Config::class);
            $container->bind(PipelineInterface::class, Pipeline::class);
            $container->bind(ListenerProviderInterface::class, ListenerProviderFactory::make($container, []));
            $container->bind(EventDispatcherInterface::class, EventDispatcherFactory::make($container));
            $container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);
            $container->bind(\Larmias\Contracts\SessionInterface::class, \Larmias\Session\Session::class);
            $container->bind(\Larmias\Contracts\ViewInterface::class, \Larmias\View\View::class);
            $container->bind(\Larmias\Http\CSRF\Contracts\CsrfManagerInterface::class, \Larmias\Http\CSRF\CsrfManager::class);
            $container->bind(\Larmias\Snowflake\Contracts\IdGeneratorInterface::class, \Larmias\Snowflake\IdGenerator::class);
            $container->bind([
                \Larmias\SharedMemory\Contracts\CommandExecutorInterface::class => \Larmias\SharedMemory\CommandExecutor::class,
                \Larmias\SharedMemory\Contracts\AuthInterface::class => \Larmias\SharedMemory\Auth::class,
                \Larmias\Contracts\TaskExecutorInterface::class => \Larmias\Task\TaskExecutor::class,
                \Larmias\Contracts\LockerInterface::class => \Larmias\Lock\Locker::class,
                \Larmias\Contracts\LockerFactoryInterface::class => \Larmias\Lock\LockerFactory::class,
                \Larmias\Crontab\Contracts\ParserInterface::class => \Larmias\Crontab\Parser::class,
                \Larmias\Crontab\Contracts\SchedulerInterface::class => \Larmias\Crontab\Scheduler::class,
                \Larmias\Crontab\Contracts\ExecutorInterface::class => \Larmias\Crontab\Executor\TaskWorkerExecutor::class,
            ]);
            $container->get(\Larmias\SharedMemory\Contracts\CommandExecutorInterface::class)->addCommand(
                \Larmias\Task\Command\TaskCommand::COMMAND_NAME, \Larmias\Task\Command\TaskCommand::class,
            );
            foreach (glob(__DIR__ . '/config/*.php') as $file) {
                $container->make(ConfigInterface::class)->load($file);
            }
            Router::init($container->make(\Larmias\Routing\Router::class));
            require __DIR__ . '/router.php';
        }
    ],
];