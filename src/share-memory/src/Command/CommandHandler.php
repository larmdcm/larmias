<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\ShareMemory\Exceptions\CommandException;
use Larmias\ShareMemory\Message\Command as MessageCommand;

class CommandHandler
{
    public static function call(string $raw): mixed
    {
        $command = static::parse($raw);
        $handler = static::getHandler($command);
        return $handler->call();
    }

    public static function parse(string $raw): MessageCommand
    {
        return MessageCommand::parse($raw);
    }

    public static function getHandler(MessageCommand $command): Command
    {
        $handlers = static::getHandlers();
        if (!isset($handlers[$command->name])) {
            throw new CommandException(sprintf('Command does not exist: %s', $command->name));
        }
        return new $handlers[$command->name]($command->args);
    }

    public static function getHandlers(): array
    {
        return [
            MessageCommand::MAP => MapCommand::class,
        ];
    }
}