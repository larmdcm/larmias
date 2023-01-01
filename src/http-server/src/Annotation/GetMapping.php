<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping extends Mapping
{
    public function __construct(string $path = '', array $options = [])
    {
        parent::__construct(['GET'], $path, $options);
    }
}