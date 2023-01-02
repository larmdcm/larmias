<?php

namespace Di;

use Larmias\Contracts\ConfigInterface;
use Larmias\Di\Annotation\Inject;

class B
{
    #[Inject]
    protected ConfigInterface $config;
}