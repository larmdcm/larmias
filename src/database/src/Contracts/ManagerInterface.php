<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Database\Query\Contracts\QueryInterface;
use Larmias\Database\Model\Contracts\QueryInterface as ModelQueryInterface;

interface ManagerInterface
{
    /**
     * 获取数据库连接
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface;

    /**
     * 实例化查询
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function newQuery(ConnectionInterface $connection): QueryInterface;

    /**
     * 实例化模型查询
     * @param ConnectionInterface $connection
     * @return ModelQueryInterface
     */
    public function newModelQuery(ConnectionInterface $connection): ModelQueryInterface;

    /**
     * 获取数据库配置
     * @param string|null $name
     * @param mixed|null $default
     * @return array
     */
    public function getConfig(?string $name = null, mixed $default = null): array;

    /**
     * 设置数据库配置
     * @param array $config
     * @return ManagerInterface
     */
    public function setConfig(array $config): ManagerInterface;

    /**
     * 获取事件调度对象
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface;

    /**
     * 设置事件调度事件
     * @param EventDispatcherInterface $eventDispatcher
     * @return ManagerInterface
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): ManagerInterface;
}