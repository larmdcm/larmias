<?php

declare(strict_types=1);

namespace Larmias\Routing\Exceptions;

class RouteNotFoundException extends RouteException
{
    public function __construct()
    {
        parent::__construct('Route Not Found',404);
    }
}