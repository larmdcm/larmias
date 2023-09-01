<?php

require __DIR__ . '/Protocol.php';

use Swoole\Coroutine\Client;

    $client = new Client(SWOOLE_UNIX_STREAM);

    var_dump($client);exit;

    $client->set(
        [
            'open_length_check'     => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
            'package_max_length'    => 1024
        ]
    );

    $client->connect(socketPipe(randomWorkerId()), 0, 3);

    $client->send(Protocol::pack('hello'));


    $ret = $client->recv();
    $client->close();

    if (! empty($ret)) {
        var_dump(Protocol::unpack($ret));
    }


/**
 * @param int $id
 * @return string
 */
function socketPipe(int $id):string
{
    return sys_get_temp_dir()."/TaskWorker.{$id}.sock";
}

/**
 * @return int
 */
function randomWorkerId(): int
{
    mt_srand();
    return rand(1, 3);
}
