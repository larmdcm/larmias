<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ApplicationInterface
{
    /**
     * @return void
     */
    public function run(): void;
}