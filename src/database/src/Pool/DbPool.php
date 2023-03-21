<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Pool\Pool;

class DbPool extends Pool
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    public function createConnection(): ConnectionInterface
    {

    }
}