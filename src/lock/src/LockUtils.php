<?php

declare(strict_types=1);

namespace Larmias\Lock;

use Larmias\Context\ApplicationContext;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Contracts\LockerInterface;
use Throwable;
use Closure;
use function call_user_func;
use function is_bool;
use function is_string;

class LockUtils
{
    /**
     * @param Key|string $key
     * @return LockerInterface
     * @throws Throwable
     */
    public static function create(Key|string $key): LockerInterface
    {
        /** @var LockerFactoryInterface $factory */
        $factory = ApplicationContext::getContainer()->get(LockerFactoryInterface::class);

        if (is_string($key)) {
            /** @var ConfigInterface $config */
            $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
            $key = new Key($key, $config->get('lock.expire', 30000));
        }
        return $factory->create($key->getName(), $key->getTtl());
    }

    /**
     * @param LockerInterface|Key|string $key
     * @param Closure|null $callback
     * @return bool
     * @throws Throwable
     */
    public static function acquire(LockerInterface|Key|string $key, ?Closure $callback = null): bool
    {
        $locker = static::getLocker($key);
        if (!$locker->acquire()) {
            return false;
        }
        return static::resolve($locker, $callback);
    }

    /**
     * @param LockerInterface|Key|string $key
     * @param int|null $waitTimeout
     * @param Closure|null $callback
     * @return bool
     * @throws Throwable
     */
    public static function block(LockerInterface|Key|string $key, ?int $waitTimeout = null, ?Closure $callback = null): bool
    {
        $locker = static::getLocker($key);
        if (!$locker->block($waitTimeout)) {
            return false;
        }
        return static::resolve($locker, $callback);
    }

    /**
     * @param LockerInterface|Key|string $key
     * @return bool
     */
    public static function release(LockerInterface|Key|string $key): bool
    {
        return static::getLocker($key)->release();
    }

    /**
     * @param LockerInterface|Key|string $key
     * @return LockerInterface
     * @throws Throwable
     */
    protected static function getLocker(LockerInterface|Key|string $key): LockerInterface
    {
        if ($key instanceof LockerInterface) {
            return $key;
        }
        return static::create($key);
    }

    /**
     * @param LockerInterface $locker
     * @param Closure|null $callback
     * @return bool
     */
    protected static function resolve(LockerInterface $locker, ?Closure $callback): bool
    {
        $result = true;
        try {
            if ($callback !== null) {
                $result = call_user_func($callback);
                $locker->release();
            }
        } catch (Throwable $e) {
            $locker->release();
            throw $e;
        }
        return is_bool($result) ? $result : true;
    }
}