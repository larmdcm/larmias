<?php

declare(strict_types=1);

namespace Larmias\Validation\Exceptions;

use RuntimeException;

class ValidateException extends RuntimeException
{
    public function __construct(protected array $errors)
    {
        $values = \array_values($this->errors);
        parent::__construct(
            rtrim(\implode(PHP_EOL, array_map(fn($item) => \implode(PHP_EOL, $item), $values)), PHP_EOL)
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}