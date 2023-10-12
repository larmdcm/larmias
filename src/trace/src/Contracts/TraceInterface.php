<?php

declare(strict_types=1);

namespace Larmias\Trace\Contracts;

interface TraceInterface
{
    /**
     * 基础信息收集
     * @var string
     */
    public const BASIC = 'basic';

    /**
     * 数据库信息收集
     * @var string
     */
    public const DATABASE = 'DATABASE';

    /**
     * 获取指定收集器
     * @param string $name
     * @return CollectorInterface
     */
    public function getCollector(string $name): CollectorInterface;

    /**
     * 获取全部收集器
     * @return CollectorInterface[]
     */
    public function getAllCollector(): array;
}