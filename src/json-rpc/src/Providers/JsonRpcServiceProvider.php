<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Providers;

use Larmias\Framework\ServiceProvider;
use Larmias\JsonRpc\Contracts\ParserInterface;
use Larmias\JsonRpc\Contracts\ProtocolInterface;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;
use Larmias\JsonRpc\JsonParser;
use Larmias\JsonRpc\Protocol\FrameProtocol;
use Larmias\JsonRpc\ServiceCollector;

class JsonRpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->bindIf([
            ServiceCollectorInterface::class => ServiceCollector::class,
            ParserInterface::class => JsonParser::class,
            ProtocolInterface::class => FrameProtocol::class,
        ]);
    }
}