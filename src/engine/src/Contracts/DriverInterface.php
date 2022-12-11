<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface DriverInterface
{
    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return string
     */
    public function getHttpServerClass(): string;

    /**
     * @return string
     */
    public function getTimerClass(): string;
}