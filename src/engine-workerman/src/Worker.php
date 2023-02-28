<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Workerman\Worker as WorkerManWorker;

class Worker extends WorkerManWorker
{
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
        $masterPid = \is_file(static::$pidFile) ? (int)\file_get_contents(static::$pidFile) : 0;
        return static::checkMasterIsAlive($masterPid) ? $masterPid : 0;
    }

    /**
     * @param string $command
     * @param bool $force
     * @return void
     */
    public static function command(string $command, bool $force = true): void
    {
        if (!\extension_loaded('posix') || !\extension_loaded('pcntl')) {
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
                    $sig = \SIGQUIT;
                    static::log("Workerman[$startFile] is gracefully stopping ...");
                } else {
                    static::$_gracefulStop = false;
                    $sig = \SIGINT;
                    static::log("Workerman[$startFile] is stopping ...");
                }
                // Send stop signal to master process.
                \posix_kill($masterPid, $sig);
                // Timeout.
                $timeout = static::$stopTimeout + 3;
                $startTime = \time();
                // Check master process is still alive?
                while (1) {
                    $master_is_alive = \posix_kill((int)$masterPid, 0);
                    if ($master_is_alive) {
                        // Timeout?
                        if (!static::$_gracefulStop && \time() - $startTime >= $timeout) {
                            static::log("Workerman[$startFile] stop fail");
                            exit;
                        }
                        // Waiting amoment.
                        \usleep(10000);
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
                \posix_kill($masterPid, $force ? \SIGUSR1 : \SIGUSR2);
                break;
        }
    }
}