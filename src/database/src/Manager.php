<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Contracts\ContainerInterface;
use Larmias\Database\Builder\MysqlBuilder;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Pool\DbProxy;
use Larmias\Database\Query\Builder;
use RuntimeException;
use function array_merge;
use function class_exists;
use function Larmias\Utils\data_get;

class Manager implements ManagerInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var ConnectionInterface[]
     */
    protected array $proxies = [];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        $name = $name ?: 'default';

        if (!isset($this->proxies[$name])) {
            if (!isset($this->config[$name])) {
                throw new RuntimeException('config not set:' . $name);
            }
            $this->config[$name]['name'] = $name;
            $this->proxies[$name] = new DbProxy($this->container, $this->config[$name]);
        }
        return $this->proxies[$name];
    }

    /**
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function query(ConnectionInterface $connection): QueryInterface
    {
        $queryClass = $connection->getConfig('query', Builder::class);
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
     * @param string|null $name
     * @param mixed|null $default
     * @return array
     */
    public function getConfig(?string $name = null, mixed $default = null): array
    {
        return data_get($this->config, $name, $default);
    }

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}