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
        $result = $this->execute($sql)->getResultSet();
        $info = [];
        foreach ($result as $item) {
            $info[$item['field']] = [
                'name' => $item['field'],
                'type' => $item['type'],
                'notnull' => 'NO' == $item['null'],
                'default' => $item['default'],
                'primary_key' => strtolower($item['key']) == 'pri',
                'auto_incr' => strtolower($item['extra']) == 'auto_increment',
                'comment' => $item['comment'],
            ];
        }

        return $info;
    }
}