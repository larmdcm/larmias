<?php

namespace Di;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ClassAnnotation
{
    public function __construct(protected mixed $value = null)
    {
    }
}