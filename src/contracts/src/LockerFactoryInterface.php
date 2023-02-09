<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface LockerFactoryInterface
{
    /**
     * @param string $key
     * @param int $ttl
     * @return LockerInterface
     */
    public function create(string $key, int $ttl): LockerInterface;
}