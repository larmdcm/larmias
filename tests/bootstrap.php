<?php

namespace Larmias\Tests;

use Larmias\Config\Config;
use Larmias\Contracts\ConfigInterface;
use Larmias\Di\Container;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Timer;
use Larmias\Utils\ApplicationContext;
use PHPUnit\TextUI\Command;

date_default_timezone_set('PRC');

function container(): ContainerInterface
{
    return Container::getInstance();
}

function init(string $env = 'swoole'): void
{
    $container = container();

    $classMap = [
        'swoole' => [
            'coroutine' => \Larmias\Engine\Swoole\Coroutine::class,
            'channel' => \Larmias\Engine\Swoole\Coroutine\Channel::class,
            'timer' => \Larmias\Engine\Swoole\Timer::class,
            'context' => \Larmias\Engine\Swoole\Context::class,
        ],
        'workerman' => [
            'coroutine' => null,
            'channel' => null,
            'timer' => \Larmias\Engine\WorkerMan\Timer::class,
            'context' => \Larmias\Engine\WorkerMan\Context::class,
        ]
    ];


    \Larmias\Engine\Coroutine::init($classMap[$env]['coroutine']);
    \Larmias\Engine\Coroutine\Channel::init($classMap[$env]['channel']);
    \Larmias\Engine\Timer::init($container->get($classMap[$env]['timer']));

    $container->bind(\Larmias\Contracts\ContextInterface::class, $classMap[$env]['context']);
    $container->bind(\Larmias\Contracts\Coroutine\CoroutineFactoryInterface::class, \Larmias\Engine\Factory\CoroutineFactory::class);
    $container->bind(\Larmias\Contracts\Coroutine\ChannelFactoryInterface::class, \Larmias\Engine\Factory\ChannelFactory::class);
    $container->bind(\Larmias\Contracts\TimerInterface::class, Timer::getTimer());
    $container->bind(ConfigInterface::class, Config::class);
    ApplicationContext::setContainer($container);
}


function main(?string $env = null): int
{
    if (!$env) {
        $env = 'workerman';
    }

    init($env);

    $callable = function (bool $exit = true) {
        Command::main($exit);
    };

    try {
        return match ($env) {
            'swoole' => swoole_co_run(function () use ($callable) {
                $callable(false);
            }),
            default => $callable(),
        };
    } finally {
        Timer::clear();
    }
}

function swoole_co_run(callable $callable): int
{
    $scheduler = new \Swoole\Coroutine\Scheduler();

    $scheduler->set([
        'hook_flags' => SWOOLE_HOOK_ALL,
    ]);

    $scheduler->add(function () use ($callable) {
        call_user_func($callable);
    });

    $scheduler->start();

    return 0;
}