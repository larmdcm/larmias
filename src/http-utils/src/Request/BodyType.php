<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request;

class BodyType
{
    /**
     * @var int
     */
    public const RAW = 1;

    /**
     * @var int
     */
    public const FORM = 2;

    /**
     * @var int
     */
    public const FORM_SERIALIZE = 3;

    /**
     * @var int
     */
    public const JSON = 4;
}