<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

use Larmias\Database\Contracts\ConnectionInterface;
use PDO;
use function array_merge;

abstract class PDOConnection implements ConnectionInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => '',
        'password' => '',
        'database' => '',
        'charset' => 'utf8mb4',
        'dsn' => '',
        'socket' => '',
        'options' => [],
        'prefix' => ''
    ];

    /**
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * @var array
     */
    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param array $config
     * @return string
     */
    abstract public function parseDsn(array $config): string;

    /**
     * @param array $config
     * @return array
     */
    public function getOptions(array $config): array
    {
        return $this->options + $config['options'];
    }

    /**
     * @param array $config
     * @return bool
     */
    public function connect(array $config = []): bool
    {
        $config = array_merge($this->config, $config);
        $this->pdo = new PDO($this->parseDsn($config), $config['username'], $config['password'], $this->getOptions($config));
        return true;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return isset($this->pdo);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        unset($this->pdo);
        return true;
    }
}