<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

use Larmias\SharedMemory\Message\Command as MessageCommand;
use Larmias\SharedMemory\Command\Command;

interface CommandExecutorInterface
{
    /**
     * @param MessageCommand $command
     * @return mixed
     */
    public function execute(MessageCommand $command): mixed;

    /**
     * @param string $raw
     * @return MessageCommand
     */
    public function parse(string $raw): MessageCommand;

    /**
     * @param MessageCommand $command
     * @return Command
     */
    public function getCommand(MessageCommand $command): Command;

    /**
     * @param string $name
     * @param string $handler
     * @return CommandExecutorInterface
     */
    public function addCommand(string $name, string $handler): CommandExecutorInterface;

    /**
     * @return array
     */
    public function getCommands(): array;
}