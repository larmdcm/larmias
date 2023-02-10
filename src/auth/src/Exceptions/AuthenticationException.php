<?php

declare(strict_types=1);

namespace Larmias\Auth\Exceptions;

use RuntimeException;
use Throwable;

class AuthenticationException extends RuntimeException
{
    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @param string $message
     * @param string|null $name
     * @param string|null $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'Unauthenticated.', ?string $name = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}