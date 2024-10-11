<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Contracts\Tcp\OnReceiveInterface;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;
use Larmias\JsonRpc\Contracts\ParserInterface;
use Larmias\JsonRpc\Message\Error;
use Larmias\JsonRpc\Message\Response;
use Throwable;
use function Larmias\Support\println;
use function Larmias\Support\format_exception;

class TcpServer implements OnReceiveInterface
{
    /**
     * @param ParserInterface $parser
     * @param ServiceCollectorInterface $collector
     */
    public function __construct(
        protected ParserInterface           $parser,
        protected ServiceCollectorInterface $collector,
    )
    {
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
            try {
                $response = new Response(error: new Error($e->getCode(), $e->getMessage()));
            } catch (Throwable $e) {
                println(format_exception($e));
            }
        } finally {
            if (isset($response)) {
                $connection->send($this->parser->encodeResponse($response));
            }
        }
    }
}