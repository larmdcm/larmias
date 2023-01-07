<?php

use think\facade\Db;

require '../bootstrap.php';

Db::setConfig([
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'debug' => true,
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => '127.0.0.1',
            // 数据库名
            'database' => 'larmias',
            // 数据库用户名
            'username' => 'larmias',
            // 数据库密码
            'password' => 'PysTpbf3LNi7dhEi',
            // 数据库连接端口
            'hostport' => '3306',
            // 数据库连接参数
            'params' => [],
            // 数据库编码默认采用utf8
            'charset' => 'utf8mb4',
            // 数据库表前缀
            'prefix' => '',
            // 断线重连
            'break_reconnect' => true,
        ],
    ],
]);

Db::listen(function ($sql,$runtime) {
    println($sql . '[' . $runtime . ']');
});

