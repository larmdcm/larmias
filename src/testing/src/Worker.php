<?php

declare(strict_types=1);

namespace Larmias\Testing;

use Larmias\Engine\Timer;
use Larmias\Engine\Worker as BaseWorker;
use PHPUnit\TextUI\Command;

class Worker extends BaseWorker
{
    /**
     * @param bool $exit
     * @return void
     * @throws \PHPUnit\TextUI\Exception
     */
    public function process(bool $exit = true): void
    {
        try {
            Command::main($exit);
        } finally {
            Timer::clear();
        }
    }
}