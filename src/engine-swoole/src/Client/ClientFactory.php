<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Client;

use Larmias\Contracts\Client\ClientFactoryInterface;
use Larmias\Contracts\Client\ClientInterface;

class ClientFactory implements ClientFactoryInterface
{
    public function dialTcp(string $host, int $port, float $timeout = 0): ClientInterface
    {
        $client = new TcpClient();

        $client->connect($host, $port, $timeout);

        return $client;
    }
}