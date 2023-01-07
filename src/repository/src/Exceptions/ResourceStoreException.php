<?php

declare(strict_types=1);

namespace Larmias\Repository\Exceptions;

use Throwable;

class ResourceStoreException extends ResourceException
{
    /**
     * ResourceStoreException __construct.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'resource not found',int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
