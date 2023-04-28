<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\ContainerInterface;
use Larmias\SharedMemory\Command\AuthCommand;
use Larmias\SharedMemory\Command\ChannelCommand;
use Larmias\SharedMemory\Command\Command;
use Larmias\SharedMemory\Command\StrCommand;
use Larmias\SharedMemory\Command\PingCommand;
use Larmias\SharedMemory\Command\SelectCommand;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\SharedMemory\Exceptions\CommandException;
use Larmias\SharedMemory\Message\Command as MessageCommand;
use function str_contains;
use function explode;

class CommandExecutor implements CommandExecutorInterface
{
    /**
     * @var string[]
     */
    protected array $commands = [
        MessageCommand::COMMAND_PING => PingCommand::class,
        MessageCommand::COMMAND_AUTH => AuthCommand::class,
        MessageCommand::COMMAND_SELECT => SelectCommand::class,
        MessageCommand::COMMAND_MAP => StrCommand::class,
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
        return $this->getCommand($command)->call();
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
        $name = str_contains($command->name, ':') ? explode(':', $command->name, 2)[0] : $command->name;
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