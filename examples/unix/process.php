<?php

use Swoole\Event;
use Swoole\Process;
use Swoole\Coroutine\Socket;
use function Swoole\Coroutine\run;

run(function () {

});

/**
 * @param int $id
 * @return string
 */
function socketPipe(int $id): string
{
    return sys_get_temp_dir() . "/TaskWorker.{$id}.sock";
}
