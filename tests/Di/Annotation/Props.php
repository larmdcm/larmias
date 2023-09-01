<?php

declare(strict_types=1);

namespace LarmiasTest\Di\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Props
{
    public function __construct(protected string $name = 'default')
    {
    }
}