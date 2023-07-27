<?php

declare(strict_types=1);

namespace Larmias\Database\Facade;

use Larmias\Database\Manager;
use Larmias\Facade\AbstractFacade;

class Db extends AbstractFacade
{
    /**
     * @return string|object
     */
    public static function getFacadeAccessor(): string|object
    {
        return Manager::class;
    }
}