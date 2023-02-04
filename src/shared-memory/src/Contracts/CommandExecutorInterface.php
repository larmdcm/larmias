<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

use Larmias\SharedMemory\Message\Command as MessageCommand;
use Larmias\SharedMemory\Command\Command;

interface CommandExecutorInterface
{
    public function execute(MessageCommand $command): mixed;

    public function parse(string $raw): MessageCommand;

    public function getCommand(MessageCommand $command): Command;

    public function addCommand(string $name, string $handler): CommandExecutorInterface;

    public function getCommands(): array;
}