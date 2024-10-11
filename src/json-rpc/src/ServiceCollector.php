<?php

declare(strict_types=1);

namespace Larmias\JsonRpc;

use Larmias\Contracts\Dispatcher\DispatcherFactoryInterface;
use Larmias\Contracts\Dispatcher\RuleInterface;
use Larmias\JsonRpc\Contracts\RequestInterface;
use Larmias\JsonRpc\Contracts\ResponseInterface;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;
use Larmias\JsonRpc\Exceptions\JsonRpcException;
use Larmias\JsonRpc\Message\Error;
use Larmias\JsonRpc\Message\Response;
use Throwable;

class ServiceCollector implements ServiceCollectorInterface
{
    /**
     * @var array
     */
    protected array $callable = [];

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
        if (is_array($services) && count($services) === count($services, 1)) {
            $services = [$services];
        }

        $services = (array)$services;
        foreach ($services as $service) {
            if (is_string($service)) {
                [$class, $method] = explode('@', $service, 2);
            } else {
                [$class, $method] = $service;
            }
            $this->callable[implode('@', [$class, $method])] = [$class, $method];
        }
        return $this;
    }

    /**
     * 注册服务类
     * @param string|array $services
     * @return ServiceCollectorInterface
     */
    public function registerService(string|array $services): ServiceCollectorInterface
    {
        $services = (array)$services;
        foreach ($services as $service) {
            $methods = get_class_methods($service);
            foreach ($methods as $method) {
                if (str_starts_with($method, '__')) {
                    continue;
                }
                $this->register([$service, $method]);
            }
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
            $handler = $this->callable[$request->getMethod()] ?? null;
            if (!$handler) {
                throw new JsonRpcException('Service does not exist:' . $request->getMethod());
            }

            $result = $this->dispatcherFactory->make(new class($handler) implements RuleInterface {
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