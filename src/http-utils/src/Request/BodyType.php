<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request;

class BodyType
{
    public const RAW = 1;

    public const FORM = 2;

    public const FORM_SERIALIZE = 3;

    public const JSON = 4;
}