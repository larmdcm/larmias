<?php

require '../bootstrap.php';

use Larmias\Command\Application;
use Larmias\Command\Command;

class TestCommand extends Command
{
    /**
     * @var string
     */
    protected string $name = 'test';

    public function handle(): void
    {
        throw new RuntimeException(__FUNCTION__);
    }
}

$container = require '../di/container.php';
$application = new Application($container);

$application->addCommand(TestCommand::class);

$application->run();