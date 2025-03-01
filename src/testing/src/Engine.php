<?php

declare(strict_types=1);

namespace Larmias\Testing;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Constants;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Event;
use Larmias\Engine\Timer;
use Larmias\Engine\WorkerConfig;
use Larmias\Engine\WorkerType;
use PHPUnit\TextUI\Command;
use RuntimeException;
use Throwable;
use function Larmias\Support\make;
use function Larmias\Support\throw_unless;
use function is_file;

class Engine
{
    /**
     * @var ContainerInterface
     */
    protected static ContainerInterface $container;

    /**
     * @var array
     */
    protected static array $config = [];

    /**
     * @param ContainerInterface $container
     * @return int
     * @throws \Throwable
     */
    public static function run(ContainerInterface $container): int
    {
        static::$container = $container;
        $config = static::getEngineConfig();
        throw_unless(isset($config['driver']), RuntimeException::class, 'config not set driver.');
        $config['settings']['mode'] = Constants::MODE_WORKER;
        /** @var KernelInterface $kernel */
        $kernel = make(KernelInterface::class);
        $kernel->setConfig(EngineConfig::build($config));
        $kernel->addWorker(WorkerConfig::build([
            'name' => 'MainProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'callbacks' => [
                Event::ON_WORKER_START => function ($worker) {
                    try {
                        Command::main(false);
                    } catch (Throwable $e) {
                        if ($e->getMessage() === 'swoole exit') {
                            return;
                        }
                        throw $e;
                    } finally {
                        Timer::clear();
                    }
                }
            ]
        ]));
        return $kernel->run();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public static function getEngineConfig(): array
    {
        $config = static::$config;
        $app = static::$container->has(ApplicationInterface::class) ? static::$container->get(ApplicationInterface::class) : null;
        if (!$app) {
            return $config;
        }

        if (method_exists($app, 'getEngineConfig')) {
            return array_merge($app->getEngineConfig(), $config);
        }

        $file = static::$container->get(ApplicationInterface::class)->getConfigPath() . DIRECTORY_SEPARATOR . 'engine.php';
        if (is_file($file)) {
            $config = array_merge(require $file, $config);
        }

        return $config;
    }

    /**
     * @param array $config
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }
}