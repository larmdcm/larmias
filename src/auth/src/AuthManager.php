<?php

declare(strict_types=1);

namespace Larmias\Auth;

use Larmias\Auth\Authentication\SessionAuthentication;
use Larmias\Auth\Authentication\TokenAuthentication;
use Larmias\Auth\Guard\SessionGuard;
use Larmias\Auth\Guard\TokenGuard;
use Larmias\Contracts\Auth\GuardInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;

class AuthManager
{
    /**
     * @var array
     */
    protected array $config = [
        'default' => 'web',
        'guards' => [
            'web' => [
                'driver' => SessionGuard::class,
                'repository' => null,
                'authentication' => SessionAuthentication::class
            ],
            'api' => [
                'driver' => TokenGuard::class,
                'repository' => null,
                'authentication' => TokenAuthentication::class
            ],
        ]
    ];

    /**
     * @var GuardInterface[]
     */
    protected array $guards = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, ConfigInterface $config)
    {
        $this->config = $config->get('auth', []);
    }

    /**
     * @param string|null $name
     * @return GuardInterface
     */
    public function guard(?string $name = null): GuardInterface
    {
        $name = $name ?: $this->config['default'];
        if (!isset($this->guards[$name])) {
            $config = $this->config['guards'][$name] ?? [];
            if (!isset($config['name'])) {
                $config['name'] = $name;
            }
            $this->guards[$name] = $this->createGuard($config);
        }
        return $this->guards[$name];
    }

    /**
     * @param array $config
     * @return GuardInterface
     */
    protected function createGuard(array $config): GuardInterface
    {
        $repository = $this->container->make($config['repository']);
        $authentication = $this->container->make($config['authentication'], ['repository' => $repository, 'config' => ['auth_name' => $config['name']]], true);
        /** @var GuardInterface $guard */
        $guard = $this->container->make($config['driver'], ['repository' => $repository, 'authentication' => $authentication], true);
        $guard->setAuthName($config['name']);
        return $guard;
    }
}