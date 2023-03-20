<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface QueryInterface
{
    /**
     * @var int
     */
    public const BUILD_SQL_INSERT = 1;

    /**
     * @var int
     */
    public const BUILD_SQL_DELETE = 2;

    /**
     * @var int
     */
    public const BUILD_SQL_UPDATE = 3;

    /**
     * @var int
     */
    public const BUILD_SQL_SELECT = 4;

    /**
     * @var int
     */
    public const BUILD_SQL_FIRST = 5;
}