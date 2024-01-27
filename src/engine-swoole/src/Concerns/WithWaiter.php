<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Concerns;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Coroutine\Waiter;

trait WithWaiter
{
    protected Waiter $waiter;

    public function initWaiter(?int $timeout = null): void
    {
        if ($timeout === null) {
            $timeout = intval($this->getWorkerConfig()->getSettings()[Constants::OPTION_MAX_WAIT_TIME] ?? 3);
            if ($timeout > 0) {
            	$timeout -= 1;
            }
        }
        $this->waiter = new Waiter($timeout);
    }
}