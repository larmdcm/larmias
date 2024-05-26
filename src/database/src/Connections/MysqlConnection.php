<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

class MysqlConnection extends PDOConnection
{
    /**
     * 解析连接信息
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

    /**
     * 获取表列信息
     * @param string $table
     * @return array
     * @throws \Throwable
     */
    public function getTableColumnInfo(string $table): array
    {
        $sql = 'SHOW FULL COLUMNS FROM ' . $table;
        $result = $this->query($sql)->getResultSet();
        $info = [];
        foreach ($result as $item) {
            $info[$item['Field']] = [
                'name' => $item['Field'],
                'type' => $item['Type'],
                'notnull' => 'NO' == $item['Null'],
                'default' => $item['Default'],
                'primary_key' => strtolower($item['Key']) == 'pri',
                'auto_incr' => strtolower($item['Extra']) == 'auto_increment',
                'comment' => $item['Comment'],
            ];
        }

        return $info;
    }
}