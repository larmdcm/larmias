<?php

declare(strict_types=1);

namespace Larmias\Database\Exceptions;

use Throwable;

class PDOException extends DBException
{
    /**
     * @var array
     */
    protected array $error;

    /**
     * @param \PDOException $PDOException
     * @param string $sql
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        \PDOException    $PDOException,
        protected array  $config = [],
        protected string $sql = '',
        int              $code = 10500,
        ?Throwable       $previous = null
    )
    {
        parent::__construct($PDOException->getMessage(), $config, $sql, $code, $previous);
        $this->error = (array)$PDOException->errorInfo;
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }
}