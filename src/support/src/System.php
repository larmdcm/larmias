<?php

declare(strict_types=1);

namespace Larmias\Support;

class System
{
    /**
     * 是否为 CLI模式
     * @return bool
     */
    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * 是否为Unix系统
     * @return bool
     */
    public static function isUnix(): bool
    {
        return DIRECTORY_SEPARATOR === '/';
    }

    /**
     * 是否为 Windows系统
     * @return bool
     */
    public static function isWindows(): bool
    {
        if (static::isMac()) {
            return false;
        }
        return stripos(PHP_OS, 'WIN') !== false;
    }

    /**
     * 是否为 Mac系统
     * @return bool
     */
    public static function isMac(): bool
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }

    /**
     * 获取cpu核心数
     * @return int
     */
    public static function getCpuCoresNum(): int
    {
        if (function_exists('swoole_cpu_num')) {
            return swoole_cpu_num();
        }

        $num = 1;

        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);

            $num = count($matches[0]);
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');

            if ($process !== false) {
                fgets($process);
                $num = intval(fgets($process));

                pclose($process);
            }
        } else {
            $process = @popen('sysctl -a', 'rb');

            if ($process !== false) {
                $output = stream_get_contents($process);

                preg_match('/hw.ncpu: (\d+)/', $output, $matches);
                if ($matches) {
                    $num = intval($matches[1][0]);
                }

                pclose($process);
            }
        }

        return $num;
    }
}