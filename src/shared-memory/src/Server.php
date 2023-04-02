<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Timer;
use Larmias\SharedMemory\Contracts\AuthInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\SharedMemory\Contracts\LoggerInterface;
use Larmias\SharedMemory\Exceptions\AuthenticateException;
use Larmias\SharedMemory\Message\Result;
use Larmias\Contracts\Worker\WorkerInterface;
use Throwable;
use function Larmias\Utils\format_exception;

class Server
{
    /**
     * @var WorkerInterface
     */
    protected WorkerInterface $worker;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Server constructor.
     * @param ContainerInterface $container
     * @param CommandExecutorInterface $executor
     * @param AuthInterface $auth
     */
    public function __construct(
        protected ContainerInterface       $container,
        protected CommandExecutorInterface $executor,
        protected AuthInterface            $auth
    )
    {
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStart(WorkerInterface $worker): void
    {
        $this->worker = $worker;

        /** @var LoggerInterface $logger */
        $logger = $this->container->make(LoggerInterface::class);
        $this->logger = $logger;
        Timer::tick($worker->getSettings('tick_interval', 1000), function () {
            $this->triggerCommand('onTick', [$this->worker]);
        });
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onConnect(ConnectionInterface $connection): void
    {
        ConnectionManager::add($connection);
        $this->logger->trace('#' . $connection->getId() . " Connected", 'info');
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
            $this->logger->trace('#' . $connection->getId() . " Received command: " . $command->name, 'info');
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
            $this->triggerCommand(__FUNCTION__, [$connection]);
            ConnectionManager::remove($connection);
            Context::clear($connection->getId());
            $this->logger->trace('#' . $connection->getId() . " Closed", 'info');
        } catch (Throwable $e) {
            $this->handleException($connection, $e);
        }
    }

    /**
     * @param string $method
     * @param array $args
     * @return void
     */
    protected function triggerCommand(string $method, array $args = []): void
    {
        foreach ($this->executor->getCommands() as $command) {
            if (\method_exists($command, $method)) {
                \call_user_func_array([$command, $method], $args);
            }
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
        $this->logger->trace($message, 'error');
    }
}