<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

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

        $sql = $event->sql;
        foreach ($event->bindings as $value) {
            $value = is_array($value) ? json_encode($value) : "'{$value}'";
            $sql = substr_replace($sql, $value, strpos($sql, '?'), 1);
        }
        \Larmias\Utils\println();
        \Larmias\Utils\println($sql);
    }
}