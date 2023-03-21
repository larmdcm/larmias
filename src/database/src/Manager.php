<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Pool\DbProxy;
use RuntimeException;
use function Larmias\Utils\data_get;
use function array_merge;

class Manager
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
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param string|null $name
     * @return QueryInterface
     */
    public function query(?string $name = null): QueryInterface
    {
        return new Query($this->createConnection($name));
    }

    /**
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function createConnection(?string $name = null): ConnectionInterface
    {
        $name = $name ?: 'default';

        if (!isset($this->proxies[$name])) {
            if (!isset($this->config[$name])) {
                throw new RuntimeException('config not set:' . $name);
            }
            $this->proxies[$name] = new DbProxy($this->config[$name]);
        }
        return $this->proxies[$name];
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