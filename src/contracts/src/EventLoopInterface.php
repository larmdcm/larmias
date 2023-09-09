<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface EventLoopInterface
{
    /**
     * 添加读事件
     * @param resource $stream
     * @param callable $func
     * @return bool
     */
    public function onReadable($stream, callable $func): bool;

    /**
     * 移除读事件
     * @param resource $stream
     * @return boolean
     */
    public function offReadable($stream): bool;

    /**
     * 添加写事件
     * @param resource $stream
     * @param callable $func
     * @return bool
     */
    public function onWritable($stream, callable $func): bool;

    /**
     * 移除写事件
     * @param resource $stream
     * @return boolean
     */
    public function offWritable($stream): bool;

    /**
     * 运行事件循环
     * @return void
     */
    public function run(): void;

    /**
     * 停止事件循环
     * @return void
     */
    public function stop(): void;
}