<?php

declare(strict_types=1);

namespace Larmias\Database\Exceptions;

use RuntimeException;
use Throwable;

class DBException extends RuntimeException
{
    /**
     * @param string $message
     * @param string $sql
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string           $message = "An exception occurred in the database.",
        protected array  $config = [],
        protected string $sql = '',
        int              $code = 10500,
        ?Throwable       $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }


    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }
}