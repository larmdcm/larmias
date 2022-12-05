<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ApplicationInterface extends ContainerInterface
{
    /**
     * @return void
     */
    public function boot(): void;

    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @return ServiceProviderInterface|null
     */
    public function register(ServiceProviderInterface|string $provider,bool $force = false): ?ServiceProviderInterface;

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getServiceProvider(ServiceProviderInterface|string $provider): ?ServiceProviderInterface;

    /**
     * @return void
     */
    public function run(): void;
}