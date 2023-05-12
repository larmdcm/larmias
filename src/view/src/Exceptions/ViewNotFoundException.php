<?php

declare(strict_types=1);

namespace Larmias\View\Exceptions;

use RuntimeException;

class ViewNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected string $template;

    /**
     * @param string $message
     * @param string $template
     * @param int $code
     */
    public function __construct(string $message = '', string $template = '', int $code = 0)
    {
        parent::__construct($message, $code);
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
}