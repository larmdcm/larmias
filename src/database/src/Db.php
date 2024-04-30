<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Facade\AbstractFacade;

/**
 * @mixin DbIDE
 */
class Db extends AbstractFacade
{
    /**
     * @return string|object
     */
    public static function getFacadeAccessor(): string|object
    {
        return ManagerInterface::class;
    }
}