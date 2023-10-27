<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Contracts;

interface WorkerInterface
{
    /**
     * @return void
     */
    public function process(): void;

    /**
     * @return int
     */
    public function getType(): int;
}