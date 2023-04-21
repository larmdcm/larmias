<?php

declare(strict_types=1);

return [
    \Larmias\Contracts\ConfigInterface::class => \Larmias\Config\Config::class,
    \Larmias\Contracts\PipelineInterface::class => \Larmias\Pipeline\Pipeline::class,
    \Larmias\Contracts\VendorPublishInterface::class => \Larmias\Framework\VendorPublish::class,
];