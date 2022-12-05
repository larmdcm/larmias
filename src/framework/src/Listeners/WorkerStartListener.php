<?php

declare(strict_types=1);

namespace Larmias\Framework\Listeners;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\Events\WorkerStart;
use Larmias\Event\Contracts\ListenerInterface;

class WorkerStartListener implements ListenerInterface
{
    /**
     * WorkerStartListener constructor.
     *
     * @param \Larmias\Contracts\ApplicationInterface $app
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
            WorkerStart::class,
        ];
    }

    /**
     * @param object $event
     */
    public function process(object $event): void
    {
        $this->app->boot();
    }
}