<?php

declare(strict_types=1);

namespace Larmias\Database\Exceptions;

use Throwable;

class ResourceNotFoundException extends QueryException
{
    /**
     * @param string $message
     * @param array $config
     * @param string $sql
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "resource not found.", array $config = [], string $sql = '', int $code = 10500, ?Throwable $previous = null)
    {
        parent::__construct(...func_get_args());
    }
}