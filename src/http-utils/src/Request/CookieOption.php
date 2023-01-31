<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request;

class CookieOption
{
    /**
     * CookieOption constructor.
     * @param string|null $content
     * @param string|null $file
     * @param string|null $savePath
     */
    public function __construct(public ?string $content = null, public ?string $file = null, public ?string $savePath = null)
    {
    }
}