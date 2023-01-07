<?php

declare(strict_types=1);

namespace Larmias\Repository\Exceptions;

use Throwable;

class ResourceDeleteException extends ResourceException
{
    /**
     * ResourceDeleteException __construct.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'resource delete fail',int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
