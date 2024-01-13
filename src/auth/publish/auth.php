<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'default' => env('AUTH_GUARD','web'),
    'guards' => [
        'web' => [
            'driver' => \Larmias\Auth\Guard\SessionGuard::class,
            'repository' => null,
            'authentication' => \Larmias\Auth\Authentication\SessionAuthentication::class
        ],
        'api' => [
            'driver' => \Larmias\Auth\Guard\TokenGuard::class,
            'repository' => null,
            'authentication' => \Larmias\Auth\Authentication\TokenAuthentication::class
        ],
    ]
];