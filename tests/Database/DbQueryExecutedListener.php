<?php

declare(strict_types=1);

namespace LarmiasTest\Database;

use Larmias\Database\Events\QueryExecuted;
use Larmias\Event\Contracts\ListenerInterface;

class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var QueryExecuted $event */
        \Larmias\Support\println();
        \Larmias\Support\println($event->connection->buildSql($event->sql, $event->bindings));
    }
}