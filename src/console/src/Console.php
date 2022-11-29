<?php

declare(strict_types=1);

namespace Larmias\Console;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;

class Console
{
    public function __construct(protected ContainerInterface $container,protected ConfigInterface $config)
    {
    }
    
    public function run(): void
    {
    }
}