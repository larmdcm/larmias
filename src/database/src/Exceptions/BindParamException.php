<?php

declare(strict_types=1);

namespace Larmias\Database\Exceptions;

use Throwable;

class BindParamException extends DBException
{
    /**
     * @param string $message
     * @param array $config
     * @param string $sql
     * @param array $binds
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string          $message = "An exception occurred in the database.",
        array           $config = [],
        string          $sql = '',
        protected array $binds = [],
        int             $code = 10502,
        ?Throwable      $previous = null
    )
    {
        parent::__construct($message, $config, $sql, $code, $previous);
    }

    /**
     * @return array
     */
    public function getBinds(): array
    {
        return $this->binds;
    }
}