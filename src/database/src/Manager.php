<?php

declare(strict_types=1);

namespace Larmias\Database;

use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Database\Query\Builder\MysqlBuilder;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Pool\DbProxy;
use Larmias\Database\Query\QueryBuilder;
use RuntimeException;
use function class_exists;
use function Larmias\Utils\data_get;
use function Larmias\Utils\throw_unless;

class Manager implements ManagerInterface
{
    /**
     * 数据库配置
     * @var array
     */
    protected array $config = [];

    /**
     * @var ConnectionInterface[]
     */
    protected array $proxies = [];

    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * 获取数据库连接
     * @param string|null $name
     * @return ConnectionInterface
     * @throws \Throwable
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        $name = $name ?: 'default';

        if (!isset($this->proxies[$name])) {
            throw_unless(isset($this->config[$name]), RuntimeException::class, 'config not set:' . $name);
            $this->config[$name]['name'] = $name;
            $proxy = new DbProxy($this->container, $this->config[$name]);
            $proxy->setEventDispatcher($this->getEventDispatcher());
            $this->proxies[$name] = $proxy;
        }

        return $this->proxies[$name];
    }

    /**
     * 实例化查询
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function newQuery(ConnectionInterface $connection): QueryInterface
    {
        $queryClass = $connection->getConfig('query', QueryBuilder::class);
        if (!class_exists($queryClass)) {
            throw new RuntimeException('query class not exists:' . $queryClass);
        }
        /** @var QueryInterface $query */
        $query = new $queryClass();
        $query->setConnection($connection);
        $query->setBuilder($this->newBuilder($connection));
        return $query;
    }

    /**
     * 实例化构造器
     * @param ConnectionInterface $connection
     * @return BuilderInterface
     */
    public function newBuilder(ConnectionInterface $connection): BuilderInterface
    {
        $builderClass = $connection->getConfig('builder', '');
        if (!$builderClass) {
            $builderClass = match ($connection->getConfig('type')) {
                'mysql' => MysqlBuilder::class,
                default => '',
            };
        }

        if (!$builderClass || !class_exists($builderClass)) {
            throw new RuntimeException('builder class not exists:' . $builderClass);
        }

        return new $builderClass($connection);
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * 获取数据库配置
     * @param string|null $name
     * @param mixed|null $default
     * @return array
     */
    public function getConfig(?string $name = null, mixed $default = null): array
    {
        return data_get($this->config, $name, $default);
    }

    /**
     * 设置数据库配置
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Manager __call.
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $name, array $args): mixed
    {
        return call_user_func_array([$this->newQuery($this->connection()), $name], $args);
    }
}