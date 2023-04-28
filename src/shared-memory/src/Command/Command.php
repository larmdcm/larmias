<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\SharedMemory\Context;
use Larmias\SharedMemory\Message\Command as MessageCommand;
use function str_contains;
use function explode;
use function call_user_func_array;

class Command
{
    /**
     * @param ContainerInterface $container
     * @param MessageCommand $command
     */
    public function __construct(protected ContainerInterface $container, protected MessageCommand $command)
    {
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        return 'ok';
    }

    /**
     * @return mixed
     */
    public function call(): mixed
    {
        $method = str_contains($this->command->name, ':') ? explode(':', $this->command->name)[1] : 'handle';
        return call_user_func_array([$this, $method], $this->command->args);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return Context::getConnection();
    }
}