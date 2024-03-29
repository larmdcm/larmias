<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request;

class Utils
{
    /**
     * @return string
     */
    public static function getDefaultUa(): string
    {
        return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
    }
}