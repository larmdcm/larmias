<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Workerman\Worker as WorkerManWorker;

class Worker extends WorkerManWorker
{
    protected static function parseCommand()
    {
        global $argv;
        $tempArgv = $argv;
        if (!isset($argv[1])) {
            $argv[1] = 'start';
        }
        parent::parseCommand();
        $argv = $tempArgv;
    }
}