<?php

declare(strict_types=1);

namespace Larmias\Server\Contracts;

interface EngineRunInterface
{
    /**
     * @return void
     */
    public static function run(): void;
}