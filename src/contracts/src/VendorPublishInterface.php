<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface VendorPublishInterface
{
    /**
     * @param string $name
     * @param array $paths
     * @return void
     */
    public function publishes(string $name, array $paths): void;

    /**
     * @param string|null $name
     * @param bool $force
     * @return void
     */
    public function handle(?string $name = null, bool $force = false): void;
}