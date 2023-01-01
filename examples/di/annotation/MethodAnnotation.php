<?php

namespace Di;

#[\Attribute(\Attribute::TARGET_METHOD)]
class MethodAnnotation
{
    public function __construct(protected mixed $value = null)
    {
    }
}