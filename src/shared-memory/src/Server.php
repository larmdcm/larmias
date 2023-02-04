<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\SharedMemory\Contracts\AuthInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\SharedMemory\Exceptions\AuthenticateException;
use Larmias\SharedMemory\Message\Result;
use Throwable;
use function Larmias\Utils\format_exception;
use function Larmias\Utils\println;
use Larmias\Engine\Contracts\WorkerInterface;

class Server
{
    /**
     * Server constructor.
     * @param ContainerInterface $container
     * @param CommandExecutorInterface $executor
     * @param AuthInterface $auth
     */
    public function __construct(
        protected ContainerInterface $container,
        protected CommandExecutorInterface $executor,
        protected AuthInterface $auth
    )
    {
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStart(WorkerInterface $worker)
    {
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onConnect(ConnectionInterface $connection): void
    {
        ConnectionManager::add($connection);
        println('#' . $connection->getId() . " Connected");
    }

    /**
     * @param ConnectionInterface $connection
     * @param string $data
     * @return void
     */
    public function onReceive(ConnectionInterface $connection, string $data): void
    {
        try {
            Context::setConnection($connection);
            $command = $this->executor->parse($data);
            println('#' . $connection->getId() . " Received command: " . $command->name);
            $this->auth->check($command);
            $result = $this->executor->execute($command);
            $connection->send($result instanceof Result ? $result->toString() : Result::build($result));
        } catch (Throwable $e) {
            $this->handleException($connection, $e);
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onClose(ConnectionInterface $connection): void
    {
        try {
            foreach ($this->executor->getCommands() as $command) {
                if (\method_exists($command, __FUNCTION__)) {
                    \call_user_func([$command, __FUNCTION__], $connection);
                }
            }
            ConnectionManager::remove($connection);
            println('#' . $connection->getId() . " Closed");
        } catch (Throwable $e) {
            $this->handleException($connection, $e);
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @param Throwable $e
     * @return void
     */
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