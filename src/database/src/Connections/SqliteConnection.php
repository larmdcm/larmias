<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

class SqliteConnection extends PDOConnection
{
    /**
     * @param array $config
     * @return string
     */
    public function parseDsn(array $config): string
    {
        return 'sqlite:' . $config['database'];
    }
}