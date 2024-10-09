<?php

declare(strict_types=1);

namespace Larmias\Auth\Guard;

use Larmias\Contracts\Auth\IdentityRepositoryInterface;
use Larmias\Contracts\Auth\AuthenticationInterface;
use Larmias\Contracts\Auth\IdentityInterface;
use Larmias\Contracts\Auth\GuardInterface;
use Larmias\Contracts\ContainerInterface;
use function method_exists;

abstract class Guard implements GuardInterface
{
    /**
     * @var string
     */
    protected string $authName = 'guard_auth_id';

    /**
     * @var IdentityInterface|null
     */
    protected ?IdentityInterface $identity = null;

    /**
     * @param ContainerInterface $container
     * @param IdentityRepositoryInterface $repository
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        protected ContainerInterface          $container,
        protected IdentityRepositoryInterface $repository,
        protected AuthenticationInterface     $authentication,
    )
    {
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @return AuthenticationInterface
     */
    public function getAuthentication(): AuthenticationInterface
    {
        return $this->authentication;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function attempt(array $params): mixed
    {
        $identity = $this->repository->findIdentity($params);
        if (!$identity) {
            return false;
        }
        return $this->login($identity);
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->identity !== null;
    }

    /**
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * @return IdentityInterface
     */
    public function user(): IdentityInterface
    {
        return $this->identity;
    }

    /**
     * @param IdentityInterface|null $user
     * @return GuardInterface
     */
    public function setUser(?IdentityInterface $user): GuardInterface
    {
        $this->identity = $user;
        return $this;
    }

    /**
     * @return int|string
     */
    public function id(): int|string
    {
        return $this->identity->getId();
    }

    /**
     * @return string
     */
    public function getAuthName(): string
    {
        return $this->authName;
    }

    /**
     * @param string $authName
     * @return self
     */
    public function setAuthName(string $authName): self
    {
        $this->authName = $authName;
        return $this;
    }
}