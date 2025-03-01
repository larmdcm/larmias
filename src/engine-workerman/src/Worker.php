<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Constants;
use RuntimeException;
use Workerman\Events\Event;
use Workerman\Events\EventInterface;
use Workerman\Events\Select;
use Workerman\Worker as BaseWorkerManWorker;
use Workerman\Connection\TcpConnection;
use Workerman\Timer as WorkerManTimer;
use function file_get_contents;
use function extension_loaded;
use function is_file;
use function time;
use function usleep;
use function in_array;
use const SIGQUIT;
use const SIGINT;
use const SIGUSR1;
use const SIGUSR2;

class Worker extends BaseWorkerManWorker
{
    /**
     * @param EngineWorker $engineWorker
     * @return Worker
     * @throws \Exception
     */
    public static function getProcessWorker(EngineWorker $engineWorker): Worker
    {
        /** @var Worker $worker */
        $worker = current(static::$workers);
        static::checkSapiEnv();
        static::init();
        static::saveMasterPid();
        static::resetStd();
        static::initWorker($engineWorker);
        return $worker;
    }

    /**
     * @param EngineWorker $engineWorker
     * @return void
     */
    public static function initWorker(EngineWorker $engineWorker): void
    {
        WorkerManTimer::delAll();
        if (!static::$globalEvent) {
            $eventLoopClass = $engineWorker->getSettings(Constants::OPTION_EVENT_LOOP_CLASS, static::getEventLoopName());
            static::$globalEvent = new $eventLoopClass;
        }
        WorkerManTimer::init(static::$globalEvent);
    }

    /**
     * @param array $config
     * @return void
     */
    public static function initConfig(array $config): void
    {
        if (isset($config[Constants::OPTION_DAEMONIZE])) {
            Worker::$daemonize = $config[Constants::OPTION_DAEMONIZE];
        }

        if (!empty($config[Constants::OPTION_EVENT_LOOP_CLASS])) {
            Worker::$eventLoopClass = $config[Constants::OPTION_EVENT_LOOP_CLASS];
        }

        if (!empty($config[Constants::OPTION_STDOUT_FILE])) {
            Worker::$stdoutFile = $config[Constants::OPTION_STDOUT_FILE];
        }

        if (!empty($config[Constants::OPTION_PID_FILE])) {
            Worker::$pidFile = $config[Constants::OPTION_PID_FILE];
        }

        if (!empty($config[Constants::OPTION_LOG_FILE])) {
            Worker::$logFile = $config[Constants::OPTION_LOG_FILE];
        }

        if (!empty($config[Constants::OPTION_PACKAGE_MAX_LENGTH])) {
            TcpConnection::$defaultMaxPackageSize = $config[Constants::OPTION_PACKAGE_MAX_LENGTH];
        }

        if (!empty($config[Constants::OPTION_SEND_PACKAGE_MAX_LENGTH])) {
            TcpConnection::$defaultMaxSendBufferSize = $config[Constants::OPTION_SEND_PACKAGE_MAX_LENGTH];
        }
    }

    /**
     * @return void
     */
    protected static function parseCommand(): void
    {
        global $argv;
        $tempArgv = $argv;
        if (!isset($argv[1])) {
            $argv[1] = 'start';
        }

        if (!in_array($argv[1], ['start', 'stop', 'restart', 'reload', 'status'])) {
            $argv[1] = 'start';
        }

        parent::parseCommand();
        $argv = $tempArgv;
    }

    /**
     * @return int
     */
    public static function getMasterPid(): int
    {
        $masterPid = is_file(self::$pidFile) ? (int)file_get_contents(self::$pidFile) : 0;
        return static::checkMasterIsAlive($masterPid) ? $masterPid : 0;
    }

    /**
     * @param string $command
     * @param bool $force
     * @return void
     */
    public static function command(string $command, bool $force = true): void
    {
        if (!extension_loaded('posix') || !extension_loaded('pcntl')) {
            return;
        }

        $masterPid = static::getMasterPid();
        if (!$masterPid) {
            return;
        }
        $startFile = $GLOBALS['argv'][0];
        switch ($command) {
            case 'stop':
            case 'restart':
                if (!$force) {
                    static::$gracefulStop = true;
                    $sig = SIGQUIT;
                    static::log("Workerman[$startFile] is gracefully stopping ...");
                } else {
                    static::$gracefulStop = false;
                    $sig = SIGINT;
                    static::log("Workerman[$startFile] is stopping ...");
                }
                // Send stop signal to master process.
                posix_kill($masterPid, $sig);
                // Timeout.
                $timeout = static::$stopTimeout + 3;
                $startTime = time();
                // Check master process is still alive?
                while (1) {
                    $masterIsAlive = posix_kill($masterPid, 0);
                    if ($masterIsAlive) {
                        // Timeout?
                        if (!static::$gracefulStop && time() - $startTime >= $timeout) {
                            static::log("Workerman[$startFile] stop fail");
                            exit;
                        }
                        // Waiting
                        usleep(10000);
                        continue;
                    }
                    // Stop success.
                    static::log("Workerman[$startFile] stop success");
                    if ($command === 'stop') {
                        exit(0);
                    }
                    break;
                }
                break;
            case 'reload':
                posix_kill($masterPid, $force ? SIGUSR1 : SIGUSR2);
                break;
        }
    }

    /**
     * @return string
     */
    protected static function getEventLoopName(): string
    {
        if (static::$eventLoopClass) {
            if (!is_subclass_of(static::$eventLoopClass, EventInterface::class)) {
                throw new RuntimeException(sprintf('%s::$eventLoopClass must implement %s', static::class, EventInterface::class));
            }
            return static::$eventLoopClass;
        }

        if (static::$globalEvent !== null) {
            static::$eventLoopClass = get_class(static::$globalEvent);
            static::$globalEvent = null;
        }

        static::$eventLoopClass = match (true) {
            extension_loaded('event') => Event::class,
            default => Select::class,
        };

        return static::$eventLoopClass;
    }
}