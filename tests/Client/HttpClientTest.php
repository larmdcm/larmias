<?php

declare(strict_types=1);

namespace LarmiasTest\Client;

use Larmias\Contracts\Client\Http\ClientFactoryInterface;
use PHPUnit\Framework\TestCase;
use function Larmias\Support\make;

class HttpClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testRequest(): void
    {
        /** @var ClientFactoryInterface $factory */
        $factory = make(ClientFactoryInterface::class);
        $httpClient = $factory->make('http://www.baidu.com');
        $response = $httpClient->request('GET');
        $this->assertSame($response->getStatusCode(), 200);
    }
}