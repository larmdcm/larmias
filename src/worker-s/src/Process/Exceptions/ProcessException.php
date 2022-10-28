<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Exceptions;

use RuntimeException;

class ProcessException extends RuntimeException
{
    /**
     * ProcessException Constructor.
     *
     * @param string $message
     * @param integer $code
     */
    public function __construct(string $message,int $code = 0)
    {
        parent::__construct($message,$code);
    }
} 