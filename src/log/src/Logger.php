<?php

declare(strict_types=1);

namespace Larmias\Log;

use Larmias\Contracts\LoggerInterface;
use Larmias\Support\Manager;
use Closure;

class Logger extends Manager implements LoggerInterface
{
    /**
     * @var string|null
     */
    protected ?string $namespace = '\\Larmias\\Logger\\Channels\\';

    /**
     * System is unusable.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Sql log info.
     *
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function sql(string|\Stringable $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        /** @var Channel $channel */
        $channel = $this->driver();
        $content = $this->format($channel, $message, $level, $context);
        $isLazy  = $this->getConfig('lazy', true);
        $channel->record($content, $level, $context, $isLazy instanceof Closure ? (bool)$isLazy($channel) : $isLazy);
    }

    /**
     * save current channel.
     *
     * @param string|null $driver
     * @return boolean
     */
    public function save(?string $driver = null): bool
    {
        if ($driver) {
            return $this->driver($driver)->save();
        }

        foreach ($this->drivers as $logger) {
            $logger->save();
        }
        return true;
    }


    /**
     * 格式化内容
     *
     * @param Channel $channel
     * @param string $message
     * @param int $level
     * @param array $context
     * @return string
     */
    protected function format(Channel $channel, string $message, int $level, array $context = []): string
    {
        $contextFormat = $this->getConfig('context_format', null) ?: function ($channel, $context) {
            return empty($context) ? '' : var_export($context, true);
        };
        $timeFormat = $this->getConfig('time_format', 'Y-m-d H:i:s');
        $contentFormat = $this->getConfig('content_format', '[:time][:level] :message[:contex]');
        $vars = [
            'time' => \date($timeFormat, time()),
            'level' => $level,
            'message' => $message,
            'context' => $contextFormat instanceof Closure ? (string)$contextFormat($channel, $context) : (string)$context,
        ];
        return $contentFormat instanceof Closure ? (string)$contentFormat($channel, $vars) : Str::parse($contentFormat, $vars);
    }

    /**
     * 获取默认驱动
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->getConfig('default');
    }

    /**
     * 获取实例配置
     *
     * @param string $driver
     * @return array
     */
    protected function resolveConfig(string $driver): array
    {
        return $this->getConfig('channels.' . $driver, []);
    }

    /**
     * 获取配置.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get('logger.' . $key, $default);
    }

    /**
     * Logger __call.
     *
     * @param string $method
     * @param array $parameters
     * @return void
     */
    public function __call($method, $parameters)
    {
        $this->log($method, ...$parameters);
    }
}