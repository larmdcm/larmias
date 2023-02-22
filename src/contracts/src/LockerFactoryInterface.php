<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface LockerFactoryInterface
{
    /**
     * @param string $name
     * @param int $ttl
     * @return LockerInterface
     */
    public function create(string $name, int $ttl): LockerInterface;
}