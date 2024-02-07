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
use Larmias\Support\Reflection\ReflectionManager;
use ReflectionMethod;
use ReflectionNamedType;
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

    protected function getMethods(string $class): array
    {
        $methods = [];
        $refClass = ReflectionManager::reflectClass($class);
        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }
            $returnType = $method->getReturnType();
            if ($returnType instanceof ReflectionNamedType) {
                $returnType = $returnType->getName();
            }
            $methods[$method->getName()] = [
                'parameters' => $this->getParameters($method),
                'returnType' => $returnType,
                'comment' => $method->getDocComment(),
            ];
        }

        return $methods;
    }

    /**
     * 获取方法参数列表
     * @param ReflectionMethod $method
     * @return array
     * @throws \ReflectionException
     */
    protected function getParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType) {
                $type = $type->getName();
            }
            $param = [
                'name' => $parameter->getName(),
                'type' => $type,
            ];

            if ($parameter->isOptional()) {
                $param['default'] = $parameter->getDefaultValue();
            }

            if ($parameter->allowsNull()) {
                $param['nullable'] = true;
            }

            $parameters[] = $param;
        }
        return $parameters;
    }


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