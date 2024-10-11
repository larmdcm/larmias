<?php

declare(strict_types=1);

namespace LarmiasTest\JsonRpc;

use Larmias\Di\Container;
use Larmias\JsonRpc\Client;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;
use Larmias\JsonRpc\Message\Request;
use Larmias\JsonRpc\ServiceCollector;
use LarmiasTest\JsonRpc\Service\UserService;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function Larmias\Support\make;

class JsonRpcTest extends TestCase
{
    public function testClientCall(): void
    {
        /** @var Client $client */
        $client = make(Client::class);

        $result = $client->call(UserService::class . '@getUserInfo', 1);

        $this->assertSame(1, $result['id']);
    }

    public function testRegisterService(): void
    {
        /** @var ServiceCollectorInterface $collector */
        $collector = Container::getInstance()->get(ServiceCollector::class);
        $collector->registerService(UserService::class);
        $collector->register([UserService::class, 'getUserInfo']);
        $collector->register([
            [UserService::class, 'getUserInfo']
        ]);
        $this->assertTrue(true);
    }

    public function testCallService(): void
    {
        /** @var ServiceCollectorInterface $collector */
        $collector = Container::getInstance()->get(ServiceCollector::class);
        $collector->registerService(UserService::class);
        $response = $collector->dispatch(new Request(UserService::class . '@getUserInfo', [1]));
        if ($response->getError()) {
            throw new RuntimeException($response->getError()->getMessage());
        }
        $this->assertSame(1, $response->getResult()['id'] ?? null);
    }
}