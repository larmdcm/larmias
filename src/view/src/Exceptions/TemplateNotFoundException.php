<?php

declare(strict_types=1);

namespace Larmias\View\Exceptions;

use RuntimeException;

class TemplateNotFoundException extends RuntimeException
{
    public string $template;

    public function __construct(string $message = '', string $template = '', int $code = 0)
    {
        parent::__construct($message, $code);
        $this->template = $template;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function __toString(): string
    {
        return \sprintf("%s:[%s]: %s->%s", __CLASS__, $this->code, $this->message, $this->template);
    }
}