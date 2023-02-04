<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\SharedMemory\Context;
use Larmias\SharedMemory\Message\Command as MessageCommand;

class Command
{
    public function __construct(protected ContainerInterface $container, protected MessageCommand $command)
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
    }

    public function handle(): string
    {
        return 'ok';
    }

    public function call(): mixed
    {
        $method = \str_contains($this->command->name, ':') ? \explode(':', $this->command->name)[1] : 'handle';
        return \call_user_func_array([$this, $method], $this->command->args);
    }

    public function getConnection(): ConnectionInterface
    {
        return Context::getConnection();
    }
}