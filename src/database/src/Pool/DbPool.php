<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Database\Connections\MysqlConnection;
use Larmias\Database\Connections\SqliteConnection;
use Larmias\Pool\Pool;
use RuntimeException;
use Throwable;
use function str_contains;
use function class_exists;

class DbPool extends Pool
{
    /**
     * @param ContainerInterface $container
     * @param array $options
     * @param array $config
     * @throws Throwable
     */
    public function __construct(ContainerInterface $container, array $options = [], protected array $config = [])
    {
        parent::__construct($container, $options);
    }

    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        $type = $this->config['type'] ?? 'mysql';

        $class = str_contains($type, '\\') ? $type : match ($type) {
            'mysql' => MysqlConnection::class,
            'sqlite' => SqliteConnection::class,
            default => '',
        };

        if (!$class || !class_exists($class)) {
            throw new RuntimeException('type class not exists:' . $class);
        }

        /** @var ConnectionInterface $connection */
        $connection = new $class($this->config);

        return $connection;
    }
}