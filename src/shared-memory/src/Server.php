<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Contracts\Tcp\OnConnectInterface;
use Larmias\Contracts\Tcp\OnReceiveInterface;
use Larmias\Contracts\Tcp\OnCloseInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\Contracts\Worker\OnWorkerStartInterface;
use Larmias\SharedMemory\Contracts\AuthInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\SharedMemory\Contracts\LockerInterface;
use Larmias\SharedMemory\Contracts\LoggerInterface;
use Larmias\SharedMemory\Exceptions\AuthenticateException;
use Larmias\SharedMemory\Message\Result;
use Larmias\Contracts\Worker\WorkerInterface;
use Throwable;
use function Larmias\Support\format_exception;
use function method_exists;
use function call_user_func_array;

class Server implements OnWorkerStartInterface, OnConnectInterface, OnReceiveInterface, OnCloseInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param ContainerInterface $container
     * @param CommandExecutorInterface $executor
     * @param AuthInterface $auth
     * @param TimerInterface $timer
     * @param ContextInterface $context
     * @param LockerInterface $locker
     */
    public function __construct(
        protected ContainerInterface       $container,
        protected CommandExecutorInterface $executor,
        protected AuthInterface            $auth,
        protected TimerInterface           $timer,
        protected ContextInterface         $context,
        protected LockerInterface          $locker,
    )
    {
        Context::init($this->context);
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStart(WorkerInterface $worker): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->container->make(LoggerInterface::class);
        $this->logger = $logger;
        $this->timer->tick($worker->getSettings('tick_interval', 1000), function () use ($worker) {
            $this->locker->tryLock(fn() => $this->triggerCommand('onTick', [$worker]));
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
     * @param mixed $data
     * @return void
     */
    public function onReceive(ConnectionInterface $connection, mixed $data): void
    {
        try {
            Context::setConnectId($connection->getId());
            $command = $this->executor->parse($data);
            $this->logger->trace('#' . $connection->getId() . " Received command: " . $command->name, 'info');
            $this->auth->check($command);
            $result = $this->locker->tryLock(fn() => $this->executor->execute($command));
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
            ConnectionManager::remove($connection);
            Context::clearConnectionData($connection->getId());
            $this->locker->tryLock(fn() => $this->triggerCommand(__FUNCTION__, [$connection]));
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
            if (method_exists($command, $method)) {
                call_user_func_array([$command, $method], $args);
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
        $connection->send($sendMessage);
        if ($e instanceof AuthenticateException) {
            $connection->close();
        }
        $this->logger->trace($message, 'error');
    }
}