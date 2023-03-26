<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface BuilderInterface
{
    /**
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function select(array $options): SqlPrepareInterface;
}