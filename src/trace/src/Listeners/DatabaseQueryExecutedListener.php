<?php

declare(strict_types=1);

namespace Larmias\Trace\Listeners;

use Larmias\Event\Contracts\ListenerInterface;
use Larmias\Database\Events\QueryExecuted;
use Larmias\Trace\Contracts\TraceContextInterface;
use Larmias\Trace\Contracts\TraceInterface;

class DatabaseQueryExecutedListener implements ListenerInterface
{
    public function __construct(protected TraceContextInterface $traceContext)
    {
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var QueryExecuted $event */
        $collector = $this->traceContext->getContextForTrace()->getCollector(TraceInterface::DATABASE);
        $collector->afterHandle([
            'sql' => $event->connection->buildSql($event->sql, $event->bindings),
            'time' => $event->time,
        ]);
    }
}