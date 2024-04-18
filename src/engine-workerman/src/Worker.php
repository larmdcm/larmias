<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Constants;
use Larmias\Engine\WorkerMan\EventDriver\Select;
use Workerman\Worker as BaseWorkerManWorker;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer as WorkerManTimer;
use function file_get_contents;
use function extension_loaded;
use function posix_kill;
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
     * @return Worker
     * @throws \Exception
     */
    public static function getProcessWorker(): Worker
    {
        /** @var Worker $worker */
        $worker = current(static::$_workers);
        static::checkSapiEnv();
        static::init();
        static::saveMasterPid();
        static::resetStd();
        static::initWorker();
        return $worker;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function initWorker(): void
    {
        WorkerManTimer::delAll();
        if (!static::$globalEvent) {
            $eventLoopClass = static::getEventLoopName();
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
                    static::$_gracefulStop = true;
                    $sig = SIGQUIT;
                    static::log("Workerman[$startFile] is gracefully stopping ...");
                } else {
                    static::$_gracefulStop = false;
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
                        if (!static::$_gracefulStop && time() - $startTime >= $timeout) {
                            static::log("Workerman[$startFile] stop fail");
                            exit;
                        }
                        // Waiting amoment.
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
     * @param $msg
     * @return void
     */
    public static function log($msg): void
    {
        if (!is_file(static::$statusFile)) {
            if (!static::$daemonize) {
                static::safeEcho($msg . PHP_EOL);
            }
            return;
        }
        parent::log($msg);
    }

    /**
     * @return string
     */
    protected static function getEventLoopName(): string
    {
        if (static::$eventLoopClass) {
            return static::$eventLoopClass;
        }

        if (!\class_exists('\Swoole\Event', false)) {
            unset(static::$_availableEventLoops['swoole']);
        }

        $loop_name = '';
        foreach (static::$_availableEventLoops as $name => $class) {
            if (\extension_loaded($name)) {
                $loop_name = $name;
                break;
            }
        }

        if ($loop_name) {
            static::$eventLoopClass = static::$_availableEventLoops[$loop_name];
        } else {
            static::$eventLoopClass = Select::class;
        }
        
        return static::$eventLoopClass;
    }
}