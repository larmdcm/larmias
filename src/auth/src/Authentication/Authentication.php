<?php

declare(strict_types=1);

namespace Larmias\Auth\Authentication;

use Larmias\Contracts\Auth\AuthenticationInterface;
use Larmias\Contracts\Auth\IdentityInterface;
use Larmias\Contracts\Auth\IdentityRepositoryInterface;

abstract class Authentication implements AuthenticationInterface
{
    /**
     * @var IdentityRepositoryInterface
     */
    protected IdentityRepositoryInterface $repository;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param IdentityRepositoryInterface $repository
     * @param array $config
     */
    public function __construct(IdentityRepositoryInterface $repository, array $config = [])
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $parameter
     * @return IdentityInterface|null
     */
    public function authenticate(mixed $parameter): ?IdentityInterface
    {
        return $this->repository->findIdentity($this->getCredentials());
    }

    /**
     * @return mixed
     */
    abstract public function getCredentials(): mixed;
}