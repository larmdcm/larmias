<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\QueryInterface;

class SqlGenerator
{
    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $builder;

    /**
     * @param QueryInterface $query
     */
    public function __construct(protected QueryInterface $query)
    {
    }

    public function select(): SqlPrepare
    {
        $sqlPrepare = new SqlPrepare();
        return $sqlPrepare;
    }
}