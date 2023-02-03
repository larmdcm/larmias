<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

use Larmias\Contracts\ContainerInterface;
use Larmias\ShareMemory\Command\AuthCommand;
use Larmias\ShareMemory\Command\ChannelCommand;
use Larmias\ShareMemory\Command\Command;
use Larmias\ShareMemory\Command\MapCommand;
use Larmias\ShareMemory\Command\PingCommand;
use Larmias\ShareMemory\Command\SelectCommand;
use Larmias\ShareMemory\Contracts\CommandExecutorInterface;
use Larmias\ShareMemory\Exceptions\CommandException;
use Larmias\ShareMemory\Message\Command as MessageCommand;

class CommandExecutor implements CommandExecutorInterface
{
    /**
     * @var array|string[]
     */
    protected array $commands = [
        MessageCommand::COMMAND_PING => PingCommand::class,
        MessageCommand::COMMAND_AUTH => AuthCommand::class,
        MessageCommand::COMMAND_SELECT => SelectCommand::class,
        MessageCommand::COMMAND_MAP => MapCommand::class,
        MessageCommand::COMMAND_CHANNEL => ChannelCommand::class,
    ];

    /**
     * CommandHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param MessageCommand $command
     * @return mixed
     */
    public function execute(MessageCommand $command): mixed
    {
        $handler = $this->getCommand($command);
        return $handler->call();
    }

    /**
     * @param string $name
     * @param string $handler
     * @return self
     */
    public function addCommand(string $name, string $handler): self
    {
        $this->commands[$name] = $handler;
        return $this;
    }

    /**
     * @param string $raw
     * @return MessageCommand
     */
    public function parse(string $raw): MessageCommand
    {
        return MessageCommand::parse($raw);
    }

    /**
     * @param MessageCommand $command
     * @return Command
     */
    public function getCommand(MessageCommand $command): Command
    {
        $commands = $this->getCommands();
        $name = \str_contains($command->name, ':') ? \explode(':', $command->name, 2)[0] : $command->name;
        if (!isset($commands[$name])) {
            throw new CommandException(sprintf('Command does not exist: %s', $command->name));
        }
        /** @var Command $result */
        $result = $this->container->make($commands[$name], ['command' => $command], true);
        return $result;
    }

    /**
     * @return string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}