<?php

declare(strict_types=1);

namespace Larmias\Validation\Contracts;

interface FormValidatorInterface
{
    public function check(array $data): bool;
}