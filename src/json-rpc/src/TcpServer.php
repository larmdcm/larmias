<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Codec\Protocol\TextProtocol;
use Larmias\Contracts\ProtocolInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Contracts\Tcp\OnReceiveInterface;
use Larmias\JsonRpc\Contracts\ParserInterface;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;
use Throwable;
use function Larmias\Support\println;
use function Larmias\Support\format_exception;

class TcpServer implements OnReceiveInterface
{
    protected ProtocolInterface $protocol;

    public function __construct(
        protected ParserInterface           $parser,
        protected ServiceCollectorInterface $collector,
    )
    {
        $this->protocol = new TextProtocol();
    }

    /**
     * 接收数据事件
     * @param ConnectionInterface $connection
     * @param mixed $data
     * @return void
     */
    public function onReceive(ConnectionInterface $connection, mixed $data): void
    {
        try {
            $request = $this->parser->decodeRequest($data);
            $response = $this->collector->dispatch($request);
        } catch (Throwable $e) {
            println(format_exception($e));
        } finally {
            if (isset($response)) {
                $connection->send($this->protocol->pack($this->parser->encodeResponse($response)));
            }
        }
    }
}