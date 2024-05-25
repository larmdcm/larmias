<?php

declare(strict_types=1);

namespace LarmiasTest\Guzzle;

use GuzzleHttp\Exception\GuzzleException;
use Larmias\Guzzle\ClientFactory;
use PHPUnit\Framework\TestCase;
use function Larmias\Support\make;

class ClientTest extends TestCase
{
    /**
     * @return void
     * @throws GuzzleException
     */
    public function testRequest(): void
    {
        /** @var ClientFactory $factory */
        $factory = make(ClientFactory::class);
        $httpClient = $factory->create([
            'base_uri' => 'https://www.baidu.com',
            'verify' => false,
        ]);
        $response = $httpClient->request('GET', '/');
        $this->assertSame($response->getStatusCode(), 200);
    }
}