<?php

declare(strict_types=1);

namespace Larmias\Console\Output\Handler;

use Larmias\Console\Contracts\OutputHandlerInterface;

class Console implements OutputHandlerInterface
{
    /** @var resource */
    protected $stdout;

    /**
     * Console constructor.
     */
    public function __construct()
    {
        $this->stdout = $this->openOutputStream();
    }

    /**
     * @param string|array $messages
     * @param bool $newline
     * @param int $type
     * @return void
     */
    public function write(string|array $messages, bool $newline = false, int $type = 0): void
    {
        $messages = (array)$messages;

        foreach ($messages as $message) {
            $this->doWrite($message, $newline);
        }
    }

    /**
     * 将消息写入到输出。
     *
     * @param string $message 消息
     * @param bool $newline 是否另起一行
     * @param resource|null $stream
     * @return void
     */
    protected function doWrite(string $message, bool $newline, $stream = null): void
    {
        if (null === $stream) {
            $stream = $this->stdout;
        }
        if (false === @fwrite($stream, $message . ($newline ? PHP_EOL : ''))) {
            throw new \RuntimeException('Unable to write output.');
        }

        fflush($stream);
    }

    /**
     * @return resource
     */
    protected function openOutputStream()
    {
        if (!$this->hasStdoutSupport()) {
            return fopen('php://output', 'w');
        }
        return @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
    }

    /**
     * 当前环境是否支持写入控制台输出到stdout.
     *
     * @return bool
     */
    protected function hasStdoutSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    /**
     * @return bool
     */
    protected function isRunningOS400(): bool
    {
        $checks = [
            function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
        ];
        return false !== stripos(implode(';', $checks), 'OS400');
    }
}