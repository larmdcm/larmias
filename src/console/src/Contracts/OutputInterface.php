<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

interface OutputInterface
{
    /**
     * 输出空行
     *
     * @param int $count
     * @return void
     */
    public function newLine(int $count = 1): void;

    /**
     * 输出信息并换行
     *
     * @param string|array $messages
     * @param int $type
     * @return void
     */
    public function writeln(string|array $messages, int $type = 0): void;

    /**
     * 输出信息
     *
     * @param string|array $messages
     * @param bool $newline
     * @param int $type
     */
    public function write(string|array $messages, bool $newline = false, int $type = 0): void;
}