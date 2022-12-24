<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\ShareMemory\Command\CommandHandler;

class Server
{
    /**
     * @var string
     */
    public const ON_RECEIVE = 'onReceive';

    public function onReceive(ConnectionInterface $connection, string $data)
    {
        Context::setId($connection->getId());
        CommandHandler::parse($data);
    }
}