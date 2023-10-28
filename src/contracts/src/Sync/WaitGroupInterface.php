<?php

declare(strict_types=1);

namespace Larmias\Contracts\Sync;

interface WaitGroupInterface
{
    /**
     * 新增计数
     * @param int $delta
     * @return void
     */
    public function add(int $delta = 1): void;

    /**
     * 完成一次计数
     * @return void
     */
    public function done(): void;

    /**
     * 等待全部完成
     * @param float $timeout
     * @return bool
     */
    public function wait(float $timeout = -1): bool;

    /**
     * @return int
     */
    public function count(): int;
}