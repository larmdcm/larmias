<?php

return [
    \Larmias\Contracts\ConfigInterface::class => \Larmias\Config\Config::class,
    \Larmias\Contracts\PipelineInterface::class => \Larmias\Pipeline\Pipeline::class,
    \Larmias\Contracts\LoggerInterface::class => \Larmias\Log\Logger::class,
    \Psr\Log\LoggerInterface::class => \Larmias\Contracts\LoggerInterface::class,
];