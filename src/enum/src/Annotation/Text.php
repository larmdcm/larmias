<?php

declare(strict_types=1);

namespace Larmias\Enum\Annotation;

use Larmias\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Text extends AbstractAnnotation
{
    /**
     * Text constructor.
     * @param string $text
     */
    public function __construct(public string $text = '')
    {
    }
}