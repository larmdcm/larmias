<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Closure;

interface ApplicationInterface
{
    /**
     * @return void
     */
    public function initialize(): void;

    /**
     * @param string $provider
     * @param bool $force
     * @return ApplicationInterface
     */
    public function register(string $provider, bool $force = false): ApplicationInterface;

    /**
     * @param string $provider
     * @return ServiceProviderInterface|null
     */
    public function getServiceProvider(string $provider): ?ServiceProviderInterface;

    /**
     * @param Closure|null $handle
     * @return void
     */
    public function discover(?Closure $handle = null): void;

    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Get the value of rootPath
     *
     * @return string
     */
    public function getRootPath(): string;

    /**
     * Set the value of rootPath
     *
     * @param string $rootPath
     * @return self
     */
    public function setRootPath(string $rootPath): self;

    /**
     * Get the value of configPath
     *
     * @return  string
     */
    public function getConfigPath(): string;

    /**
     * Set the value of configPath
     *
     * @param string $configPath
     * @return self
     */
    public function setConfigPath(string $configPath): self;

    /**
     * Get the value of runtimePath
     *
     * @return string
     */
    public function getRuntimePath(): string;

    /**
     * Set the value of runtimePath
     *
     * @param string $runtimePath
     * @return self
     */
    public function setRuntimePath(string $runtimePath): ApplicationInterface;

    /**
     * @return string
     */
    public function getConfigExt(): string;

    /**
     * @param string $configExt
     * @return self
     */
    public function setConfigExt(string $configExt): ApplicationInterface;

    /**
     * @return bool
     */
    public function isDiscovering(): bool;

    /**
     * @param bool $isDiscovering
     * @return ApplicationInterface
     */
    public function setIsDiscovering(bool $isDiscovering): ApplicationInterface;
}