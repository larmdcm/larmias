<?php

declare(strict_types=1);

namespace Larmias\Log;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Utils\Arr;

class Logger implements LoggerInterface
{
    /**
     * @var \Larmias\Log\Channel[]
     */
    protected array $channels = [];

    /**
     * Logger constructor.
     *
     * @param \Larmias\Contracts\ContainerInterface $container
     * @param \Larmias\Contracts\ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

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
        $this->record($message, (string)$level, $context);
    }

    /**
     * 写日志
     *
     * @param string|\Stringable $message
     * @param string $level
     * @param array $context
     * @return bool
     */
    public function write(string|\Stringable $message, string $level, array $context = []): bool
    {
        return $this->record($message, $level, $context, true);
    }


    /**
     * 记录日志
     *
     * @param string|\Stringable $message
     * @param string $level
     * @param array $context
     * @param bool $realtimeWrite
     * @return bool
     */
    public function record(string|\Stringable $message, string $level, array $context = [], bool $realtimeWrite = false): bool
    {
        $name = $this->getConfig('level_channels.' . $level);
        return $this->channel($name)->record($message, $level, $context, $realtimeWrite);
    }

    /**
     * save current channel.
     *
     * @param string|null $name
     * @return boolean
     */
    public function save(?string $name = null): bool
    {
        if ($name) {
            return $this->channel($name)->save();
        }

        foreach ($this->channels as $channel) {
            $channel->save();
        }
        return true;
    }

    /**
     * @param string|null $name
     * @return \Larmias\Log\Channel
     */
    public function channel(?string $name = null): Channel
    {
        $name = $name ?: $this->getConfig('default');
        $handlers = [];
        $channelConfig = $this->getConfig('channels.' . $name);
        foreach (Arr::wrap($channelConfig['handler']) as $item) {
            $handlerConfig = $this->getConfig('handlers.' . $item);
            $handlers[] = $this->container->make($handlerConfig['class'], $handlerConfig['constructor'] ?? [], true);
        }

        $formatterConfig = $this->getConfig('formatters.' . $channelConfig['formatter']);
        /** @var \Larmias\Log\Contracts\FormatterInterface $formatter */
        $formatter = $this->container->make($formatterConfig['class'], $formatterConfig['constructor'] ?? [], true);
        $allowLevel = $channelConfig['level'] ?? $this->getConfig('level', []);
        $realtimeWrite = $channelConfig['realtime_write'] ?? $this->getConfig('realtime_write', true);
        return new Channel($name, $handlers, $formatter, $allowLevel, $realtimeWrite);
    }

    /**
     * 获取配置.
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if (\is_null($name)) {
            return $this->config->get('logger');
        }
        return $this->config->get('logger.' . $name, $default);
    }


    /**
     * Logger __call.
     *
     * @param string $method
     * @param array $parameters
     * @return void
     */
    public function __call(string $method, array $parameters)
    {
        $this->log($method, ...$parameters);
    }
}