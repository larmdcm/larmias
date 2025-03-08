<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Concerns\WithIdAtomic;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Server as TcpServer;
use Swoole\Coroutine\Server\Connection as TcpConnection;
use Swoole\Exception as SwooleException;
use Larmias\Engine\Event;
use Throwable;

class Server extends BaseServer
{
    use WithIdAtomic;

    /**
     * @var TcpServer
     */
    protected TcpServer $server;

    /**
     * @return void
     * @throws SwooleException
     */
    public function process(): void
    {
        $this->initServer();

        $this->server = new TcpServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
            $this->getSettings(Constants::OPTION_SSL, false),
            $this->getSettings(Constants::OPTION_REUSE_PORT, true)
        );

        $this->server->set($this->getServerSettings());

        $protocol = $this->getSettings('protocol');

        $this->server->handle(function (TcpConnection $tcpConnection) use ($protocol) {
            try {
                $connection = new Connection($this->generateId(), $tcpConnection);

                $connection->setOptions([
                    'protocol' => $protocol,
                ]);

                $connection->startCoRecv();
                $this->join($connection);

                $this->waiter->add(fn() => $this->trigger(Event::ON_CONNECT, [$connection]));

                while ($this->running) {
                    $data = $connection->recv();
                    if ($data === '' || $data === false) {
                        break;
                    }
                    $this->waiter->add(function () use ($connection, $data) {
                        $this->connHeartbeatCheck($connection);
                        $this->trigger(Event::ON_RECEIVE, [$connection, $data]);
                        $connection->processing = false;
                    });
                }
                $connection->close();
                $this->waiter->add(fn() => $this->trigger(Event::ON_CLOSE, [$connection]));
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        });

        $this->waiter->add(fn() => $this->start());

        $this->wait(fn() => $this->shutdown());
    }

    /**
     * @return void
     */
    public function onServerStart(): void
    {
        $this->server->start();
    }

    /**
     * @return void
     */
    public function onServerShutdown(): void
    {
        $this->server->shutdown();
    }
}