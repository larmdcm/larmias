<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface DriverInterface
{
    /**
     * @param KernelInterface $kernel
     * @return void
     */
    public function run(KernelInterface $kernel): void;

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void;

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void;

    /**
     * @param array $config
     * @return void
     */
    public function setConfig(array $config = []): void;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $name, mixed $default = null): mixed;

    /**
     * @return string|null
     */
    public function getTcpServerClass(): ?string;

    /**
     * @return string|null
     */
    public function getUdpServerClass(): ?string;

    /**
     * @return string|null
     */
    public function getHttpServerClass(): ?string;

    /**
     * @return string|null
     */
    public function getWebSocketServerClass(): ?string;

    /**
     * @return string|null
     */
    public function getProcessClass(): ?string;

    /**
     * @return string|null
     */
    public function getEventLoopClass(): ?string;

    /**
     * @return string|null
     */
    public function getTimerClass(): ?string;

    /**
     * @return string|null
     */
    public function getSignalClass(): ?string;

    /**
     * @return string|null
     */
    public function getContextClass(): ?string;

    /**
     * @return string|null
     */
    public function getCoroutineClass(): ?string;

    /**
     * @return string|null
     */
    public function getChannelClass(): ?string;
}