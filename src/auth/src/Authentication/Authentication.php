<?php

declare(strict_types=1);

namespace Larmias\Auth\Authentication;

use Larmias\Contracts\Auth\AuthenticationInterface;
use Larmias\Contracts\Auth\IdentityInterface;
use Larmias\Contracts\Auth\IdentityRepositoryInterface;
use Larmias\Contracts\ContainerInterface;
use function array_merge;
use function method_exists;

abstract class Authentication implements AuthenticationInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'auth_name' => 'auth_id',
    ];

    /**
     * @param ContainerInterface $container
     * @param IdentityRepositoryInterface $repository
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, protected IdentityRepositoryInterface $repository, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @param mixed $parameter
     * @return IdentityInterface|null
     */
    public function authenticate(mixed $parameter): ?IdentityInterface
    {
        $id = $this->getCredentials($parameter);
        return $id ? $this->repository->findIdentity(array_merge($this->config, ['credentials' => $id])) : null;
    }

    /**
     * @param mixed $parameter
     * @return mixed
     */
    abstract public function getCredentials(mixed $parameter): mixed;
}