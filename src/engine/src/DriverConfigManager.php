<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Closure;
use Larmias\Engine\Drivers\WorkerSDriver;
use Larmias\Engine\Drivers\WorkerS\HttpServer as WorkerSHttpServer;
use RuntimeException;

class DriverConfigManager
{
    /** @var string */
    public const WORKER_S = 'workerS';

    /** @var string */
    public const WORKER_MAN = 'workerMan';

    /** @var DriverConfig[] */
    protected static array $driverConfigs = [];

    /** @var bool */
    protected static bool $isInit= false;

    /**
     * @param string $name
     * @param DriverConfig|Closure $config
     * @return void
     */
    public static function register(string $name, DriverConfig|Closure $config): void
    {
        static::$driverConfigs[$name] = $config;
    }

    /**
     * @return void
     */
    public static function init(): void
    {
        if (static::$isInit) {
            return;
        }
        static::$isInit = true;

        static::register(static::WORKER_S,function (): DriverConfig {
            return DriverConfig::build([
                'driver' => WorkerSDriver::class,
                'http_server' => WorkerSHttpServer::class,
            ]);
        });
    }

    /**
     * @param string $name
     * @return DriverConfig
     */
    public static function get(string $name): DriverConfig
    {
        $driverConfig = static::$driverConfigs[$name] ?? null;
        if ($driverConfig instanceof Closure) {
            $driverConfig = $driverConfig();
        }
        if (!($driverConfig instanceof  DriverConfig)) {
            throw new RuntimeException($name . ' driver config not set.');
        }
        return $driverConfig;
    }
}