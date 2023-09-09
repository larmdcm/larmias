<?php

declare(strict_types=1);

namespace Larmias\Engine\Coroutine;

use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Contracts\Coroutine\LockerInterface;

class Locker implements LockerInterface
{
    /** @var string */
    public const LOCK_KEY = 'larmias_co_lock';

    /**
     * @var array
     */
    protected static array $container = [];

    public function __construct(protected CoroutineInterface $coroutine)
    {
    }


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

        self::$container[$key][] = $this->coroutine->id();

        $this->coroutine->yield();

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
                    $this->coroutine->resume($id);
                }
            }
        }

        return true;
    }
}