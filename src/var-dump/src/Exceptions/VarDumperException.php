<?php

declare(strict_types=1);

namespace Larmias\VarDumper\Exceptions;

use RuntimeException;
use Throwable;

class VarDumperException extends RuntimeException
{
    /**
     * @param array $vars
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(protected array $vars = [], string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }
}