<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Query\Contracts\QueryInterface;
use Larmias\Database\Query\Query;
use Larmias\Database\Model\Contracts\QueryInterface as ModelQueryInterface;
use Larmias\Database\Model\Query as ModelQuery;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Database\Query\Builder\MysqlBuilder;
use Larmias\Database\Contracts\SqlBuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Pool\DbProxy;
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
        $queryClass = $connection->getConfig('query_class', Query::class);
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
     * 实例化模型查询
     * @param ConnectionInterface $connection
     * @return ModelQueryInterface
     */
    public function newModelQuery(ConnectionInterface $connection): ModelQueryInterface
    {
        $queryClass = $connection->getConfig('model_query_class', ModelQuery::class);
        if (!class_exists($queryClass)) {
            throw new RuntimeException('model query class not exists:' . $queryClass);
        }

        /** @var ModelQueryInterface $query */
        $query = new $queryClass();
        $query->setConnection($connection);
        $query->setBuilder($this->newBuilder($connection));
        return $query;
    }

    /**
     * 实例化构造器
     * @param ConnectionInterface $connection
     * @return SqlBuilderInterface
     */
    public function newBuilder(ConnectionInterface $connection): SqlBuilderInterface
    {
        $builderClass = $connection->getConfig('builder_class', '');
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
     * @return ManagerInterface
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): ManagerInterface
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
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
     * @return ManagerInterface
     */
    public function setConfig(array $config): ManagerInterface
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