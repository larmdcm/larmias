<?php

declare(strict_types=1);

namespace Larmias\Validation\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Validate
{
    /**
     * @param string $validate
     * @param string|null $scene
     * @param bool $batch
     */
    public function __construct(public string $validate, public ?string $scene = null, public bool $batch = false)
    {
    }
}