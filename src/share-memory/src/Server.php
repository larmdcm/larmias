<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\ShareMemory\Contracts\AuthInterface;
use Larmias\ShareMemory\Contracts\CommandHandlerInterface;
use Larmias\ShareMemory\Exceptions\AuthenticateException;
use Larmias\ShareMemory\Message\Result;
use Throwable;
use function Larmias\Utils\format_exception;
use function Larmias\Utils\println;

class Server
{
    public function __construct(
        protected ContainerInterface $container,
        protected CommandHandlerInterface $handler,
        protected AuthInterface $auth
    )
    {
    }

    public function onConnect(ConnectionInterface $connection): void
    {
        ConnectionManager::add($connection);
        println('#' . $connection->getId() . " Connected");
    }

    public function onReceive(ConnectionInterface $connection, string $data): void
    {
        try {
            Context::setConnection($connection);
            $command = $this->handler->parse($data);
            println('#' . $connection->getId() . " Received command: " . $command->name);
            $this->auth->check($command);
            $result = $this->handler->handle($command);
            $connection->send($result instanceof Result ? $result->toString() : Result::build($result));
        } catch (Throwable $e) {
            $this->handleException($connection, $e);
        }
    }

    public function onClose(ConnectionInterface $connection): void
    {
        try {
            foreach ($this->handler->getHandlers() as $handler) {
                if (\method_exists($handler, __FUNCTION__)) {
                    \call_user_func([$handler, __FUNCTION__], $connection);
                }
            }
            ConnectionManager::remove($connection);
            println('#' . $connection->getId() . " Closed");
        } catch (Throwable $e) {
            $this->handleException($connection, $e);
        }
    }

    protected function handleException(ConnectionInterface $connection, Throwable $e): void
    {
        $message = format_exception($e);
        $sendMessage = Result::build($e->getMessage(), false);
        if ($e instanceof AuthenticateException) {
            $connection->close($sendMessage);
        } else {
            $connection->send($sendMessage);
        }
        println($message);
    }
}