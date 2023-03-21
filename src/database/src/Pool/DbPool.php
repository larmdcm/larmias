<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Database\Connections\MysqlConnection;
use Larmias\Pool\Pool;
use RuntimeException;
use function str_contains;
use function class_exists;

class DbPool extends Pool
{
    /**
     * @param array $options
     * @param array $config
     */
    public function __construct(array $options = [], protected array $config = [])
    {
        parent::__construct($options);
    }

    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        $type = $this->config['type'] ?? 'mysql';

        $class = str_contains($type, '\\') ? $type : match ($type) {
            'mysql' => MysqlConnection::class,
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