<?php

declare(strict_types=1);

namespace Larmias\Database\Listener;

use Larmias\Event\Contracts\ListenerInterface;
use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Database\Events\QueryExecuted;

class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @param StdoutLoggerInterface $logger
     */
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    /**
     * @return string[]
     */
    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param object $event
     * @return void
     */
    public function process(object $event): void
    {
        /** @var QueryExecuted $event */
        $sql = $event->sql;
        $sql = $event->connection->buildSql($sql, $event->bindings);
        $this->logger->sql($sql);
    }
}