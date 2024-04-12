<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'default' => env('FILE_DRIVER', 'local'),
    'storage' => [
        'local' => [
            'driver' => null,
            'root' => __DIR__ . '/../../runtime',
        ],
    ],
];