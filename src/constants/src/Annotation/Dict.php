<?php

declare(strict_types=1);

namespace Larmias\Constants\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Dict
{
    /**
     * @param array $value
     */
    public function __construct(public array $value = [])
    {
    }
}