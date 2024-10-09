<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Contracts\Dispatcher\DispatcherFactoryInterface;
use Larmias\Contracts\Dispatcher\RuleInterface;
use Larmias\JsonRpc\Contracts\RequestInterface;
use Larmias\JsonRpc\Contracts\ResponseInterface;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;
use Larmias\JsonRpc\Message\Error;
use Larmias\JsonRpc\Message\Response;
use Throwable;

class ServiceCollector implements ServiceCollectorInterface
{
    /**
     * @var array
     */
    protected array $services = [];

    public function __construct(protected DispatcherFactoryInterface $dispatcherFactory)
    {
    }

    /**
     * 注册服务
     * @param string|array $services
     * @return ServiceCollectorInterface
     */
    public function register(string|array $services): ServiceCollectorInterface
    {
        $services = (array)$services;
        foreach ($services as $service) {
            $this->services[$service] = [
                'class' => $service,
            ];
        }
        return $this;
    }

    /**
     * 调度服务
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request): ResponseInterface
    {
        $response = new Response();
        $response->setId($request->getId());
        try {
            $result = $this->dispatcherFactory->make(new class($request->getMethod()) implements RuleInterface {
                public function __construct(protected mixed $handler)
                {
                }

                public function getHandler(): array
                {
                    return $this->handler;
                }

                public function getOption(?string $name = null): array
                {
                    return [];
                }
            })->dispatch($request->getParams());
            $response->setResult($result);
        } catch (Throwable $e) {
            $response->setError(new Error($e->getCode(), $e->getMessage()));
        }

        return $response;
    }
}