<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Contracts;

interface ServiceCollectorInterface
{
    /**
     * 注册服务
     * @param string|array $services
     * @return ServiceCollectorInterface
     */
    public function register(string|array $services): ServiceCollectorInterface;

    /**
     * 注册服务类
     * @param string|array $services
     * @return ServiceCollectorInterface
     */
    public function registerService(string|array $services): ServiceCollectorInterface;

    /**
     * 服务调度
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request): ResponseInterface;
}