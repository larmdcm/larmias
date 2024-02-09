<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

interface ClientFactoryInterface
{
    /**
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @return ClientInterface
     */
    public function dialTcp(string $host, int $port, float $timeout = 0): ClientInterface;
}