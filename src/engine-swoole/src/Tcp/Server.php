<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Engine\Swoole\Contracts\PackerInterface;
use Larmias\Engine\Swoole\Packer\Buffer;
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
                $connection = new Connection($tcpConnection, $this->packer);
                $this->trigger(Event::ON_CONNECT, [$connection]);
                $buffer = new Buffer();
                while (true) {
                    $data = $connection->recv();
                    if ($data === '' || $data === false) {
                        break;
                    }

                    $buffer->append($data);
                    $bfString = $buffer->toString();

                    while (!empty($bfString)) {
                        try {
                            $unpack = $this->packer->unpack($bfString);
                        } catch (Throwable $e) {
                            $this->printException($e);
                            $buffer->flush();
                        }
                        if (empty($unpack)) {
                            break;
                        }
                        $buffer->write($unpack[1]);
                        $bfString = $buffer->toString();
                        $this->trigger(Event::ON_RECEIVE, [$connection, $unpack[0]]);
                    }
                }
                $this->trigger(Event::ON_CLOSE, [$connection]);
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