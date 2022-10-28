<?php

declare(strict_types=1);

namespace Larmias\Server;

class ServerEngine
{
    /** @var string */
    public const ENGINE_WORKERS = 'workerS';

    /** @var string */
    public const ENGINE_WORKER_MAN = 'workerMan';

    /** @var array */
    protected static array $engines = [
        self::ENGINE_WORKERS => [ServerEngine::class, 'startWorkerS'],
        self::ENGINE_WORKER_MAN => [ServerEngine::class, 'startWorkerMan'],
    ];

    /**
     * @param string $name
     * @param callable $handler
     * @return void
     */
    public static function registerEngine(string $name, callable $handler): void
    {
        static::$engines[$name] = $handler;
    }

    /**
     * @return void
     */
    public static function run(string $engine): void
    {
        $handler = static::$engines[$engine] ?? null;
        if (!is_callable($handler)) {
            throw new \RuntimeException($engine . ' engine is not a callable.');
        }
        call_user_func($handler);
    }

    /**
     * @return void
     */
    public static function startWorkerS(): void
    {
        \Larmias\WorkerS\WorkerS::runAll();
    }

    /**
     * @return void
     */
    public static function startWorkerMan(): void
    {
    }
}