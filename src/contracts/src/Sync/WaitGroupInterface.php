<?php

declare(strict_types=1);

namespace Larmias\Contracts\Sync;

interface WaitGroupInterface
{
    /**
     * 新增计数
     * @param int $num
     * @return void
     */
    public function add(int $num = 1): void;

    /**
     * 完成一次计数
     * @return void
     */
    public function done(): void;

    /**
     * 等待全部完成
     * @return void
     */
    public function wait(): void;
}