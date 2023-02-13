<?php

declare(strict_types=1);

namespace Larmias\Framework\Listeners;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Event\Contracts\ListenerInterface;
use Larmias\Command\Events\BeforeHandle;

class BeforeHandleListener implements ListenerInterface
{
    /**
     * WorkerStartListener constructor.
     *
     * @param ApplicationInterface $app
     */
    public function __construct(protected ApplicationInterface $app)
    {
    }

    /**
     * @return array
     */
    public function listen(): array
    {
        return [
            BeforeHandle::class,
        ];
    }

    /**
     * @param object $event
     */
    public function process(object $event): void
    {
        $this->app->initialize();
    }
}