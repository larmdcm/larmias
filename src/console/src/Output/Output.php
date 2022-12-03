<?php

declare(strict_types=1);

namespace Larmias\Console\Output;

use Larmias\Console\Contracts\OutputInterface;

class Output implements OutputInterface
{
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
     * @param string $messages
     * @param int $type
     * @return void
     */
    public function writeln(string $messages, int $type = 0): void
    {
        $this->write($messages, true, $type);
    }

    /**
     * 输出信息
     *
     * @param string $messages
     * @param bool $newline
     * @param int $type
     */
    public function write(string $messages, bool $newline = false, int $type = 0): void
    {
    }
}