<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Workerman\Connection\TcpConnection;
use Workerman\Worker as BaseWorkerManWorker;
use function is_file;
use function file_get_contents;
use function extension_loaded;
use function posix_kill;
use function time;
use function usleep;
use const SIGQUIT;
use const SIGINT;
use const SIGUSR1;
use const SIGUSR2;

class Worker extends BaseWorkerManWorker
{
    /**
     * @param array $config
     * @return void
     */
    public static function initConfig(array $config): void
    {
        if (isset($config['daemonize'])) {
            Worker::$daemonize = $config['daemonize'];
        }

        if (!empty($config['event_loop_class'])) {
            Worker::$eventLoopClass = $config['event_loop_class'];
        }

        if (!empty($config['stdout_file'])) {
            Worker::$stdoutFile = $config['stdout_file'];
        }

        if (!empty($config['pid_file'])) {
            Worker::$pidFile = $config['pid_file'];
        }

        if (!empty($config['log_file'])) {
            Worker::$logFile = $config['log_file'];
        }

        if (!empty($config['max_send_buffer_size'])) {
            TcpConnection::$defaultMaxSendBufferSize = $config['max_send_buffer_size'];
        }

        if (!empty($config['max_package_size'])) {
            TcpConnection::$defaultMaxPackageSize = $config['max_package_size'];
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
}