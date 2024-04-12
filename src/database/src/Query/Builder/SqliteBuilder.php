<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Builder;

class SqliteBuilder extends Builder
{
    /**
     * @param string $field
     * @return string
     */
    public function escapeField(string $field): string
    {
        return $field;
    }

    /**
     * @param string $limit
     * @param string|null $offset
     * @return string
     */
    public function buildLimit(string $limit, ?string $offset = null): string
    {
        return $offset ? ' LIMIT ' . $limit . ' OFFSET ' . $offset . ' ' : ' LIMIT ' . $limit . ' ';
    }

    /**
     * @param string $lockType
     * @return string
     */
    public function parseLock(string $lockType): string
    {
        return '';
    }
}