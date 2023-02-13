<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands;

use Larmias\Command\Command as BaseCommand;
use Larmias\Contracts\ApplicationInterface;

abstract class Command extends BaseCommand
{
    /**
     * @param ApplicationInterface $app
     */
    public function __construct(protected ApplicationInterface $app)
    {
        parent::__construct();
    }
}