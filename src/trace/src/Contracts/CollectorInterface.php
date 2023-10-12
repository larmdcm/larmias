<?php

declare(strict_types=1);

namespace Larmias\Trace\Contracts;

interface CollectorInterface
{
    /**
     * 前置收集处理
     * @param array $args
     * @return void
     */
    public function beforeHandle(array $args): void;

    /**
     * 后置收集处理
     * @param array $args
     * @return void
     */
    public function afterHandle(array $args): void;

    /**
     * 收集数据
     * @param mixed $data
     * @return void
     */
    public function collect(mixed $data): void;

    /**
     * 获取收集的全部数据
     * @return array
     */
    public function all(): array;
}