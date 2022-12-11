<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Larmias\Console\Command as BaseCommand;
use Larmias\Contracts\ApplicationInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Engine\Events\WorkerStart;

abstract class Command extends BaseCommand
{
    public function __construct(protected ApplicationInterface $app,protected EventDispatcherInterface $dispatcher)
    {
        parent::__construct();
        $this->dispatcher->dispatch(new WorkerStart());
    }
}