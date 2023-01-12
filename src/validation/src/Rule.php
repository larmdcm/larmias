<?php

declare(strict_types=1);

namespace Larmias\Validation;

use Closure;
use Larmias\Utils\Str;

class Rule
{
    public function __construct(protected string $name, protected array|Closure $args = [])
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array|Closure
     */
    public function getArgs(): array|Closure
    {
        return $this->args;
    }

    /**
     * @param string $name
     * @param array $args
     * @return \Larmias\Validation\Rule
     */
    public static function __callStatic(string $name, array $args): Rule
    {
        return new static(Str::studly($name), $args[0] ?? []);
    }
}