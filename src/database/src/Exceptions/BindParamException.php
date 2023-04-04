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
     * @param array $bindings
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string          $message = "An exception occurred in the database.",
        array           $config = [],
        string          $sql = '',
        protected array $bindings = [],
        int             $code = 10502,
        ?Throwable      $previous = null
    )
    {
        parent::__construct($message, $config, $sql, $code, $previous);
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}