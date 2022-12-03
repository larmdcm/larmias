<?php

declare(strict_types=1);

namespace Larmias\Console\Output;

use Larmias\Console\Contracts\OutputHandlerInterface;
use Larmias\Console\Contracts\OutputInterface;
use Larmias\Console\Output\Handler\Console;

class Output implements OutputInterface
{
    protected OutputHandlerInterface $handler;

    /**
     * Output constructor.
     */
    public function __construct()
    {
        $this->handler = new Console();
    }

    /**
     * 输出空行
     *
     * @param int $count
     * @return void
     */
    public function newLine(int $count = 1): void
    {
        $this->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * 输出信息并换行
     *
     * @param string|array $messages
     * @param int $type
     * @return void
     */
    public function writeln(string|array $messages, int $type = 0): void
    {
        $this->write($messages, true, $type);
    }

    /**
     * 输出信息
     *
     * @param string|array $messages
     * @param bool $newline
     * @param int $type
     */
    public function write(string|array $messages, bool $newline = false, int $type = 0): void
    {
        $this->handler->write($messages, $newline, $type);
    }
}