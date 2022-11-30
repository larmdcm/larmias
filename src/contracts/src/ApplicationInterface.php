<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ApplicationInterface extends ContainerInterface
{
    /**
     * @return void
     */
    public function initialize(): void;

    /**
     * @return void
     */
    public function run(): void;
}