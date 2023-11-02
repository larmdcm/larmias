<?php

declare(strict_types=1);

namespace Larmias\Coroutine\Sync;

use Larmias\Contracts\Sync\LockerInterface;
use Larmias\Coroutine\Coroutine;

class Locker implements LockerInterface
{
    /** @var string */
    public const LOCK_KEY = 'larmias_co_lock';

    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * 加锁
     * @param string|null $key
     * @return bool
     */
    public function lock(?string $key = null): bool
    {
        if (!$key) {
            $key = self::LOCK_KEY;
        }

        if (!isset(self::$container[$key])) {
            self::$container[$key][] = 0;
            return true;
        }

        self::$container[$key][] = Coroutine::id();

        Coroutine::yield();

        return false;
    }

    /**
     * 解锁
     * @param string|null $key
     * @return bool
     */
    public function unlock(?string $key = null): bool
    {
        if (!$key) {
            $key = self::LOCK_KEY;
        }

        if (isset(self::$container[$key])) {
            $ids = self::$container[$key];
            foreach ($ids as $id) {
                if ($id > 0) {
                    Coroutine::resume($id);
                }
            }
        }

        return true;
    }
}