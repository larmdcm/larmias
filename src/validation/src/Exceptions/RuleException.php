<?php

declare(strict_types=1);

namespace Larmias\Validation\Exceptions;

use Larmias\Validation\Rule;
use RuntimeException;

class RuleException extends RuntimeException
{
    public function __construct(string $message, protected ?Rule $rule = null)
    {
        parent::__construct($message);
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }
}