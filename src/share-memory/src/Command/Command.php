<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\Contracts\ContainerInterface;
use Larmias\ShareMemory\Message\Command as MessageCommand;

abstract class Command
{
    public function __construct(protected ContainerInterface $container,protected MessageCommand $command)
    {
    }

    public function handle(): string
    {
        return 'ok';
    }

    public function call(): mixed
    {
        $method = \str_contains($this->command->name,':') ? \explode(':',$this->command->name)[1] : 'handle';
        return \call_user_func_array([$this, $method],$this->command->args);
    }
}