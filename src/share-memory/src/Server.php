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
        Context::setConnection($connection);
    }

    public function onReceive(ConnectionInterface $connection, string $data): void
    {
        try {
            $command = $this->handler->parse($data);
            $this->auth->check($command);
            $result = $this->handler->handle($command);
            $connection->send($result instanceof Result ? $result->toString() : Result::build($result));
        } catch (Throwable $e) {
            $this->handleException($connection,$e);
        }
    }

    protected function handleException(ConnectionInterface $connection,Throwable $e): void
    {
        $message = $e->getFile() . '('. $e->getLine() .')' . ':' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        $sendMessage = Result::build($e->getMessage(),false);
        if ($e instanceof AuthenticateException) {
            $connection->close($sendMessage);
        } else {
            $connection->send($sendMessage);
        }
        echo $message . PHP_EOL;
    }
}