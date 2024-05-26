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

    /**
     * 获取表列信息
     * @param string $table
     * @return array
     * @throws \Throwable
     */
    public function getTableColumnInfo(string $table): array
    {
        $sql = 'PRAGMA table_info( \'' . $table . '\' )';
        $result = $this->query($sql)->getResultSet();
        $info = [];

        foreach ($result as $item) {
            $info[$item['name']] = [
                'name' => $item['name'],
                'type' => $item['type'],
                'notnull' => 1 === $item['notnull'],
                'default' => $item['dflt_value'],
                'primary_key' => '1' == $item['pk'],
                'auto_incr' => '1' == $item['pk'],
            ];
        }
        return $info;
    }
}