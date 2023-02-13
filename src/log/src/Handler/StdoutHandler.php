<?php

declare(strict_types=1);

namespace Larmias\Log\Handler;

use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Log\Contracts\LoggerHandlerInterface;
use Larmias\Log\LoggerLevel;

class StdoutHandler implements LoggerHandlerInterface
{
    /**
     * @var resource
     */
    protected static $outputStream;

    /**
     * availableForegroundColors
     *
     * @var array
     */
    protected static array $availableForegroundColors = [
        'black' => ['set' => 30, 'unset' => 39],
        'red' => ['set' => 31, 'unset' => 39],
        'green' => ['set' => 32, 'unset' => 39],
        'yellow' => ['set' => 33, 'unset' => 39],
        'blue' => ['set' => 34, 'unset' => 39],
        'magenta' => ['set' => 35, 'unset' => 39],
        'cyan' => ['set' => 36, 'unset' => 39],
        'white' => ['set' => 37, 'unset' => 39],
    ];

    /**
     * availableBackgroundColors
     *
     * @var array
     */
    protected static array $availableBackgroundColors = [
        'black' => ['set' => 40, 'unset' => 49],
        'red' => ['set' => 41, 'unset' => 49],
        'green' => ['set' => 42, 'unset' => 49],
        'yellow' => ['set' => 43, 'unset' => 49],
        'blue' => ['set' => 44, 'unset' => 49],
        'magenta' => ['set' => 45, 'unset' => 49],
        'cyan' => ['set' => 46, 'unset' => 49],
        'white' => ['set' => 47, 'unset' => 49],
    ];

    /**
     * @param StdoutLoggerInterface|null $logger
     */
    public function __construct(protected ?StdoutLoggerInterface $logger = null)
    {
    }


    /**
     * @param array $logs
     * @return bool
     */
    public function save(array $logs): bool
    {
        foreach ($logs as $logList) {
            foreach ($logList as $logItem) {
                if ($this->logger) {
                    $this->logger->log($logItem['level'], $logItem['content']);
                } else {
                    static::safeEcho(static::getLevelStyleText($logItem['content'], $logItem['level']));
                }
            }
        }
        return true;
    }

    /**
     * @param string $message
     * @param string $level
     * @return string
     */
    public static function getLevelStyleText(string $message, string $level): string
    {
        $code = static::getStyleCode($level);
        return static::getStyleText($message, $code[0], $code[1]);
    }

    /**
     * 获取样式code.
     *
     * @param string $level
     * @return array
     */
    public static function getStyleCode(string $level): array
    {
        $codes = ['set' => [], 'unset' => []];
        switch ($level) {
            case LoggerLevel::DEBUG:
            case LoggerLevel::SQL:
                $codes['set'][] = static::$availableForegroundColors['green']['set'];
                $codes['unset'][] = static::$availableForegroundColors['green']['unset'];
                break;
            case LoggerLevel::INFO:
                $codes['set'][] = static::$availableForegroundColors['cyan']['set'];
                $codes['unset'][] = static::$availableForegroundColors['cyan']['unset'];
                break;
            case LoggerLevel::WARNING:
                $codes['set'][] = static::$availableForegroundColors['yellow']['set'];
                $codes['unset'][] = static::$availableForegroundColors['yellow']['unset'];
                break;
            case LoggerLevel::ERROR:
                $codes['set'][] = static::$availableForegroundColors['red']['set'];
                $codes['unset'][] = static::$availableForegroundColors['red']['unset'];
                break;
            case LoggerLevel::CRITICAL:
                $codes['set'][] = static::$availableForegroundColors['blue']['set'];
                $codes['unset'][] = static::$availableForegroundColors['blue']['unset'];
                break;
            case LoggerLevel::ALERT:
                $codes['set'][] = static::$availableForegroundColors['magenta']['set'];
                $codes['unset'][] = static::$availableForegroundColors['magenta']['unset'];
                break;
            case LoggerLevel::EMERGENCY:
                $codes['set'][] = static::$availableBackgroundColors['red']['set'];
                $codes['unset'][] = static::$availableBackgroundColors['red']['unset'];
                $codes['set'][] = static::$availableForegroundColors['white']['set'];
                $codes['unset'][] = static::$availableForegroundColors['white']['unset'];
                break;
            default:
                $codes['set'][] = static::$availableForegroundColors['black']['set'];
                $codes['unset'][] = static::$availableForegroundColors['black']['unset'];
                break;
        }
        return [$codes['set'], $codes['unset']];
    }

    /**
     * 获取带样式的文本
     *
     * @param string $content
     * @param array $setCodes
     * @param array $unsetCodes
     * @return string
     */
    public static function getStyleText(string $content, array $setCodes, array $unsetCodes): string
    {
        return \sprintf("\033[%sm%s\033[%sm", \implode(';', $setCodes), $content, \implode(';', $unsetCodes));
    }


    /**
     * Safe Echo.
     *
     * @param string $message
     * @return bool
     */
    public static function safeEcho(string $message): bool
    {
        $stream = static::outputStream();
        if (!$stream) {
            return false;
        }
        \fwrite($stream, $message);
        \fflush($stream);
        return true;
    }

    /**
     * set and get output stream.
     *
     * @param resource|null $stream
     * @return resource|bool
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
        return static::$outputStream = $stream;
    }
}