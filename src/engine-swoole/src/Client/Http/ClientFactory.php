<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Client\Http;

use Larmias\Contracts\Client\Http\ClientFactoryInterface;
use Larmias\Contracts\Client\Http\ClientException;
use Larmias\Contracts\Client\Http\ClientInterface;

class ClientFactory implements ClientFactoryInterface
{
    /**
     * @param string $url
     * @return ClientInterface
     */
    public function make(string $url): ClientInterface
    {
        $result = parse_url($url);
        if (!$result) {
            throw new ClientException('url parse error:' . $url);
        }
        $scheme = $result['scheme'] ?? 'http';
        return new Client($result['host'], $result['port'] ?? 80, $scheme === 'https');
    }
}