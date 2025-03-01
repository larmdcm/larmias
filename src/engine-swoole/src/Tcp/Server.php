<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Concerns\WithIdAtomic;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Server as CoServer;
use Swoole\Coroutine\Server\Connection as TcpConnection;
use Swoole\Exception as SwooleException;
use Larmias\Engine\Event;
use Throwable;

class Server extends BaseServer
{
    use WithIdAtomic;

    /**
     * @var CoServer
     */
    protected CoServer $server;

    /**
     * @return void
     * @throws SwooleException
     */
    public function process(): void
    {
        $this->initServer();
        $this->initIdAtomic();
        $this->initWaiter();

        $this->server = new CoServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
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
                    $this->waiter->add(fn() => $this->trigger(Event::ON_RECEIVE, [$connection, $data]));
                }
                $connection->close();
                $this->waiter->add(fn() => $this->trigger(Event::ON_CLOSE, [$connection]));
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        });

        $this->waiter->add(fn() => $this->server->start());

        $this->wait(fn() => $this->shutdown());
    }

    /**
     * @return void
     */
    public function serverShutdown(): void
    {
        $this->server->shutdown();
    }
}