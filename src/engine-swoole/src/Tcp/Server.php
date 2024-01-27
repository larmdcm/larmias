<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Concerns\WithIdAtomic;
use Larmias\Engine\Swoole\Contracts\PackerInterface;
use Larmias\Engine\Swoole\Packer\Buffer;
use Larmias\Engine\Swoole\Packer\EmptyPacker;
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
     * @var PackerInterface
     */
    protected PackerInterface $packer;

    /**
     * @return void
     * @throws SwooleException
     */
    public function process(): void
    {
        $this->initIdAtomic();
        $this->initWaiter();

        $this->server = new CoServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
            $this->getSettings(Constants::OPTION_SSL, false),
            $this->getSettings(Constants::OPTION_REUSE_PORT, true)
        );

        $this->server->set($this->getServerSettings());

        $this->packer = $this->newPacker();

        $this->server->handle(function (TcpConnection $tcpConnection) {
            try {
                $connection = new Connection($this->generateId(), $tcpConnection, $this->packer);

                $this->waiter->add(fn() => $this->trigger(Event::ON_CONNECT, [$connection]));

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
                        $this->waiter->add(fn() => $this->trigger(Event::ON_RECEIVE, [$connection, $unpack[0]]));
                    }
                }
                $connection->close();
                $this->waiter->add(fn() => $this->trigger(Event::ON_CLOSE, [$connection]));
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        });

        $this->waiter->add(fn() => $this->server->start());

        $this->wait(fn() => $this->server->shutdown());
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