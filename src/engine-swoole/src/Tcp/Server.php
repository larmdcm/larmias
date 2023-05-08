<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Engine\Swoole\Contracts\PackerInterface;
use Larmias\Engine\Swoole\Packer\EmptyPacker;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Server as TCPServer;
use Swoole\Coroutine\Server\Connection as TCPConnection;
use Swoole\Exception as SwooleException;
use Larmias\Engine\Event;
use Throwable;

class Server extends BaseServer
{
    /**
     * @var TCPServer
     */
    protected TCPServer $server;

    /**
     * @var PackerInterface
     */
    protected PackerInterface $packer;

    /**
     * @return void
     * @throws SwooleException
     */
    public function process(): void
    {
        $this->server = new TCPServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
            $this->getSettings('ssl', false),
            $this->getSettings('reuse_port', true)
        );

        $this->server->set($this->getServerSettings());

        $this->packer = $this->newPacker();

        $this->server->handle(function (TCPConnection $tcpConnection) {
            try {
                $connection = new Connection($tcpConnection);
                // $this->trigger(Event::ON_CONNECT, [$connection]);
                while (true) {
                    $data = $tcpConnection->recv();
                    if ($data === '' || $data === false) {
                        break;
                    }
                    begin:
                    $data = $this->packer->unpack($data);

                    // $this->trigger(Event::ON_RECEIVE, [$connection, $data]);

                }
                // $this->trigger(Event::ON_CLOSE, [$connection]);
                $connection->close();
            } catch (Throwable $e) {
                $this->exceptionHandler($e);
            }
        });

        $this->server->start();
    }

    /**
     * @return PackerInterface
     */
    protected function newPacker(): PackerInterface
    {
        $packerClass = $this->getSettings('packer', EmptyPacker::class);
        return new $packerClass();
    }
}