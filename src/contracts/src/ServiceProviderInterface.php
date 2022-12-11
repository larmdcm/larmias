<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ServiceProviderInterface
{
    /**
     * @return void
     */
    public function register(): void;

    /**
     * @return void
     */
    public function boot(): void;
}