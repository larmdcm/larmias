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
     * @return void
     */
    public function reload(): void;

    /**
     * @return string
     */
    public function getTcpServerClass(): string;

    /**
     * @return string
     */
    public function getHttpServerClass(): string;

    /**
     * @return string
     */
    public function getProcessClass(): string;

    /**
     * @return string
     */
    public function getTimerClass(): string;
}