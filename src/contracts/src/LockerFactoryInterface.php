<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface LockerFactoryInterface
{
    /**
     * 创建Locker
     * @param string $name
     * @param int $ttl
     * @return LockerInterface
     */
    public function create(string $name, int $ttl): LockerInterface;
}