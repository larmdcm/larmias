<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ApplicationInterface
{
    /** @var int */
    public const STATUS_NORMAL = 1;

    /** @var int */
    public const STATUS_PRELOAD = 2;

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
     * @param int $status
     * @return ApplicationInterface
     */
    public function setStatus(int $status): ApplicationInterface;

    /**
     * @return int
     */
    public function getStatus(): int;
}