<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

abstract class Mapping
{
    public function __construct(public array $methods = [], public string $path = '', public array $options = [])
    {
    }
}