<?php
declare(strict_types=1);

class Identity implements \Larmias\Contracts\Auth\IdentityInterface
{
    public function getId(): string|int
    {
        return 1;
    }
}

class IdentityRepository implements \Larmias\Contracts\Auth\IdentityRepositoryInterface
{

    public function findIdentity(mixed $parameter): ?\Larmias\Contracts\Auth\IdentityInterface
    {
        return new Identity();
    }
}

return [
    'default' => 'web',
    'guards' => [
        'web' => [
            'driver' => \Larmias\Auth\Guard\SessionGuard::class,
            'repository' => IdentityRepository::class,
            'authentication' => \Larmias\Auth\Authentication\SessionAuthentication::class
        ]
    ]
];