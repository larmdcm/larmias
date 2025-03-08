<?php

declare(strict_types=1);

namespace Larmias\Guzzle;

use Larmias\Contracts\Client\Http\ClientFactoryInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Larmias\Guzzle\Handler\CoroutineHandler;

class ClientFactory
{
    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context)
    {
    }

    /**
     * @param array $config
     * @return Client
     */
    public function create(array $config = []): Client
    {
        if ($this->context->inCoroutine() && $this->container->has(ClientFactoryInterface::class)) {
            /** @var callable $coHandler */
            $coHandler = $this->container->make(CoroutineHandler::class);
            $stack = HandlerStack::create($coHandler);
            $config = array_replace(['handler' => $stack], $config);
        }

        return new Client($config);
    }
}