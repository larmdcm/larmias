<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

use Larmias\Di\Annotation\AnnotationAbstract;

abstract class Mapping extends AnnotationAbstract
{
    public function __construct(public array $methods = [],public string $path = '',public array $options = [])
    {
    }
}