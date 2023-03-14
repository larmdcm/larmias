<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

class MysqlConnection extends PDOConnection
{
    /**
     * @param array $config
     * @return string
     */
    public function parseDsn(array $config): string
    {
        if (!empty($config['dsn'])) {
            return $config['dsn'];
        }

        if (!empty($config['socket'])) {
            $dsn = 'mysql:unix_socket=' . $config['socket'];
        } elseif (!empty($config['port'])) {
            $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'];
        } else {
            $dsn = 'mysql:host=' . $config['host'];
        }
        $dsn .= ';dbname=' . $config['database'];

        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }

        return $dsn;
    }
}