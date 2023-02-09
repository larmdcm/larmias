<?php

declare(strict_types=1);

namespace Larmias\Auth\Guard;

use Larmias\Contracts\Auth\GuardInterface;
use Larmias\Contracts\Auth\IdentityInterface;

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