<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Support;

use Throwable;

class Helper
{
    /**
     * @var mixed
     */
    protected static $outputDecorated;

    /**
     * @var resource
     */
    protected static $outputStream;

    /**
     * @return boolean
     */
    public static function isWin(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * @return boolean
     */
    public static function isUnix(): bool
    {
        return DIRECTORY_SEPARATOR === '/';
    }

    /**
     * 获取是否支持异步信号
     * 
     * @return bool
     */
    public static function isSupportAsyncSignal(): bool
    {
        if (!static::isUnix()) {
            return false;
        }
        return \function_exists('pcntl_async_signals');
    }

    /**
     * 设置进程标题
     *
     * @param string $name
     * @return bool
     */
    public static function setProcessTitle(string $name): bool
    {
        try {
            return \cli_set_process_title($name);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getRuntimePath(string $path = ''): string
    {
        $runtimePath = Env::get('runtime_path',sys_get_temp_dir());
        return static::getDirPath($runtimePath) . DIRECTORY_SEPARATOR . rtrim($path,DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getChannelPath(string $path = ''): string
    {
        return static::getDirPath(static::getRuntimePath('channels')) . DIRECTORY_SEPARATOR . rtrim($path,DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getLogPath(string $path = ''): string
    {
        return static::getDirPath(static::getRuntimePath('logs')) . DIRECTORY_SEPARATOR . rtrim($path,DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getLockPath(string $path = ''): string
    {
        return static::getDirPath(static::getRuntimePath('locks')) . DIRECTORY_SEPARATOR . rtrim($path,DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getDirPath(string $path): string
    {
        \is_dir($path) || \mkdir($path,0775,true);
        return $path;
    }

    /**
     * @param string $file
     * @return string
     */
    public static function getFilePath(string $file): string
    {
        if (!\file_exists($file)) {
            \touch($file);
        }
        return $file;
    }

    /**
     * Safe Echo.
     * 
     * @param string $msg
     * @param bool $decorated
     * @return bool
     */
    public static function safeEcho(string $msg, bool $decorated = false): bool
    {
        $stream = static::outputStream();
        if (!$stream) {
            return false;
        }
        if (!$decorated) {
            $line = $white = $green = $end = '';
            if (static::$outputDecorated) {
                $line = "\033[1A\n\033[K";
                $white = "\033[47;30m";
                $green = "\033[32;40m";
                $end = "\033[0m";
            }
            $msg = \str_replace(['<n>', '<w>', '<g>'], [$line, $white, $green], $msg);
            $msg = \str_replace(['</n>', '</w>', '</g>'], $end, $msg);
        } elseif (!static::$outputDecorated) {
            return false;
        }
        \fwrite($stream, $msg);
        \fflush($stream);
        return true;
    }

    /**
     * set and get output stream.
     *
     * @param resource|null $stream
     * @return false|resource
     */
    private static function outputStream($stream = null)
    {
        if (!$stream) {
            $stream = static::$outputStream ?: \STDOUT;
        }
        if (!$stream || !\is_resource($stream) || 'stream' !== \get_resource_type($stream)) {
            return false;
        }
        $stat = \fstat($stream);
        if (!$stat) {
            return false;
        }

        if (($stat['mode'] & 0170000) === 0100000) { // whether is regular file
            static::$outputDecorated = false;
        } else {
            static::$outputDecorated =
                \DIRECTORY_SEPARATOR === '/' && // linux or unix
                \function_exists('posix_isatty') &&
                \posix_isatty($stream); // whether is interactive terminal
        }
        return static::$outputStream = $stream;
    }
}