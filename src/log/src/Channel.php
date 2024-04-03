<?php

declare(strict_types=1);

namespace Larmias\Log;

use Larmias\Contracts\Logger\ChannelInterface;
use Larmias\Log\Contracts\FormatterInterface;
use Larmias\Log\Events\LogWrite;
use Psr\EventDispatcher\EventDispatcherInterface;
use function in_array;

class Channel implements ChannelInterface
{
    /**
     * @var array
     */
    protected array $logs = [];

    /**
     * @param string $name
     * @param array $handlers
     * @param FormatterInterface $formatter
     * @param array $allowLevel
     * @param bool $realtimeWrite
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        protected string                    $name,
        protected array                     $handlers,
        protected FormatterInterface        $formatter,
        protected array                     $allowLevel = [],
        protected bool                      $realtimeWrite = true,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    )
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
     * Logger __call.
     * @param string $method
     * @param array $parameters
     * @return void
     */
    public function __call(string $method, array $parameters)
    {
        $this->log($method, ...$parameters);
    }

    /**
     * 写日志
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
     * @param string|\Stringable $message
     * @param string $level
     * @param array $context
     * @param bool|null $realtimeWrite
     * @return bool
     */
    public function record(string|\Stringable $message, string $level, array $context = [], ?bool $realtimeWrite = null): bool
    {
        if (!empty($this->allowLevel) && !in_array($level, $this->allowLevel)) {
            return true;
        }

        $this->logs[$level][] = [
            'message' => $message,
            'level' => $level,
            'context' => $context,
            'content' => $this->formatter->format($message, $this->name, $level, $context)
        ];

        if ($realtimeWrite === null) {
            $realtimeWrite = $this->realtimeWrite;
        }

        if ($realtimeWrite) {
            return $this->save();
        }
        return true;
    }

    /**
     * 保存
     * @return bool
     */
    public function save(): bool
    {
        if (!empty($this->logs)) {
            $this->eventDispatcher?->dispatch(new LogWrite($this->logs, $this->name));
            foreach ($this->handlers as $handler) {
                $handler->save($this->logs);
            }
            $this->clear();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isRealtimeWrite(): bool
    {
        return $this->realtimeWrite;
    }

    /**
     * @param bool $realtimeWrite
     */
    public function setRealtimeWrite(bool $realtimeWrite): void
    {
        $this->realtimeWrite = $realtimeWrite;
    }

    /**
     * 清除内存日志信息
     *
     * @return void
     */
    public function clear(): void
    {
        $this->logs = [];
    }
}