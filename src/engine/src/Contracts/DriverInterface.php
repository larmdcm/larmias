<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface DriverInterface
{
    /**
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
    public function restart(bool $force = true): void;

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void;

    /**
     * @return string
     */
    public function getTcpServerClass(): string;

    /**
     * @return string
     */
    public function getUdpServerClass(): string;

    /**
     * @return string
     */
    public function getHttpServerClass(): string;

    /**
     * @return string
     */
    public function getWebSocketServerClass(): string;

    /**
     * @return string
     */
    public function getProcessClass(): string;

    /**
     * @return string
     */
    public function getEventLoopClass(): string;

    /**
     * @return string
     */
    public function getTimerClass(): string;

    /**
     * @return string
     */
    public function getSignalClass(): string;

    /**
     * @return string
     */
    public function getContextClass(): string;
}