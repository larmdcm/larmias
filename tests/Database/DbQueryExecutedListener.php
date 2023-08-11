<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Events\QueryExecuted;
use Larmias\Database\Facade\Db;
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
        \Larmias\Utils\println();
        \Larmias\Utils\println(Db::getConnection()->buildSql($event->sql, $event->bindings));
    }
}