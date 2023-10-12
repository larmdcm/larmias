<?php

declare(strict_types=1);

return [
    'http' => [
        'collectors' => [
            \Larmias\Trace\Contracts\TraceInterface::BASIC => \Larmias\Trace\Collectors\HttpBasicCollector::class,
            \Larmias\Trace\Contracts\TraceInterface::DATABASE => \Larmias\Trace\Collectors\DatabaseCollector::class,
        ]
    ]
];