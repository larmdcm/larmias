<?php

declare(strict_types=1);

namespace Larmias\Console\Contracts;

interface OutputInterface
{
    /** @var int */
    public const OUTPUT_NORMAL = 0;
    /** @var int */
    public const OUTPUT_RAW    = 1;
    /** @var int */
    public const OUTPUT_PLAIN  = 2;

    /**
     * @param string $message
     * @return void
     */
    public function info(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function error(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function comment(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function question(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function highlight(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function warning(string $message): void;

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

    /**
     * @return OutputTableInterface
     */
    public function table(): OutputTableInterface;
}