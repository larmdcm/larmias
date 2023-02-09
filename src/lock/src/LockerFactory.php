<?php

declare(strict_types=1);

namespace Larmias\Lock;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Contracts\LockerInterface;

class LockerFactory implements LockerFactoryInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param string $key
     * @param int $ttl
     * @return LockerInterface
     */
    public function create(string $key, int $ttl): LockerInterface
    {
        $lockKey = new Key($key, $ttl);
        /** @var LockerInterface $locker */
        $locker = $this->container->make(LockerInterface::class, ['key' => $lockKey], true);
        return $locker;
    }
}