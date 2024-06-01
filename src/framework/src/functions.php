<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Engine\Constants;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Event;
use Larmias\Engine\WorkerConfig;
use Larmias\Engine\WorkerType;
use RuntimeException;
use function Larmias\Support\invoke;
use function Larmias\Support\make;
use function is_array;
use function Larmias\Support\throw_unless;
use function str_starts_with;

/**
 * 在引擎容器中运行回调
 * @param callable $callback
 * @param array $config
 * @return void
 * @throws \Throwable
 */
function run(callable $callback, array $config = []): void
{
    $app = app();
    if (method_exists($app, 'getEngineConfig')) {
        $config = array_merge($app->getEngineConfig(), $config);
    }
    throw_unless(isset($config['driver']), RuntimeException::class, 'config not set driver.');
    $settings = $config['settings'] ?? [];
    $settings['mode'] = Constants::MODE_WORKER;
    /** @var KernelInterface $kernel */
    $kernel = make(KernelInterface::class);
    $kernel->setConfig(EngineConfig::build([
        'driver' => $config['driver'],
        'settings' => $settings,
    ]));
    $kernel->addWorker(WorkerConfig::build([
        'name' => $config['main_process_name'] ?? 'MainProcess',
        'type' => WorkerType::WORKER_PROCESS,
        'callbacks' => [
            Event::ON_WORKER_START => function ($worker) use ($callback, $kernel) {
                invoke($callback, [$worker, $kernel]);
            }
        ]
    ]));
    $kernel->run();
}

/**
 * 获取app实例对象
 * @return ApplicationInterface
 */
function app(): ApplicationInterface
{
    /** @var ApplicationInterface $app */
    $app = make(ApplicationInterface::class);
    return $app;
}

/**
 * 框架配置操作
 * @param mixed $key
 * @param mixed $value
 * @return mixed
 */
function config(mixed $key = null, mixed $value = null): mixed
{
    /** @var ConfigInterface $config */
    $config = make(ConfigInterface::class);
    if ($key === null && $value === null) {
        return $config;
    }
    if (is_array($key)) {
        return $config->set($key);
    }
    return str_starts_with($key, '?') ? $config->has($key) : $config->get($key, $value);
}

/**
 * 获取控制台输出实例
 * @return StdoutLoggerInterface
 */
function console(): StdoutLoggerInterface
{
    /** @var StdoutLoggerInterface $console */
    $console = make(StdoutLoggerInterface::class);
    return $console;
}