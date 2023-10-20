<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Sync\LockerInterface as CoLockerInterface;
use Larmias\SharedMemory\Contracts\LockerInterface;
use Closure;

class Locker implements LockerInterface
{
    /**
     * @param ContextInterface $context
     * @param CoLockerInterface $coLocker
     */
    public function __construct(protected ContextInterface $context, protected CoLockerInterface $coLocker)
    {
    }

    /**
     * @param Closure $handler
     * @return mixed
     */
    public function tryLock(Closure $handler): mixed
    {
        if (!$this->context->inCoroutine()) {
            $result = $handler();
        } else {
            try {
                $this->coLocker->lock();
                $result = $handler();
            } finally {
                $this->coLocker->unlock();
            }
        }

        return $result;
    }
}