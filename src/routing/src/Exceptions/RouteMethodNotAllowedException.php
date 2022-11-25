<?php

declare(strict_types=1);

namespace Larmias\Routing\Exceptions;

class RouteMethodNotAllowedException extends RouteException
{
    public function __construct()
    {
        parent::__construct('Route Method Not Allowed',404);
    }
}