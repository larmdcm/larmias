<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process;

use Larmias\WorkerS\Process\Exceptions\ProcessException;
use Larmias\WorkerS\Support\Helper;

class Daemonize
{
    /**
     * @return void
     */
    public static function run(): void
    {
        if (!Helper::isUnix()) {
            return;
        }
        $pid = \pcntl_fork();
        if ($pid === 0) {
            $sid = \posix_setsid();
            if ($sid === -1) {
                throw new ProcessException("set sid error.");
            }
            if (\chdir('/') === false) {
                throw new ProcessException("chdir change dir path error.");
            }
            \umask(0);
            \fclose(STDIN);
            \fclose(STDOUT);
            \fclose(STDERR);
        } else if ($pid > 0) {
            exit(0);
        } else {
            throw new ProcessException('fork process error.');
        }
    }
}