<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Contracts;

use Larmias\ShareMemory\Message\Command as MessageCommand;

interface CommandHandlerInterface
{
    public function handle(MessageCommand $command): mixed;

    public function parse(string $raw): MessageCommand;

    public function addHandler(string $name, string $handler): CommandHandlerInterface;

    public function getHandlers(): array;
}