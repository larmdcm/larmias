<?php

declare(strict_types=1);

namespace Larmias\Contracts\Auth;

interface GuardInterface
{
    /**
     * @return AuthenticationInterface
     */
    public function getAuthentication(): AuthenticationInterface;

    /**
     * @param array $params
     * @return mixed
     */
    public function attempt(array $params): mixed;

    /**
     * @return bool
     */
    public function check(): bool;

    /**
     * @return bool
     */
    public function guest(): bool;

    /**
     * @return IdentityInterface
     */
    public function user(): IdentityInterface;

    /**
     * @param IdentityInterface|null $user
     * @return GuardInterface
     */
    public function setUser(?IdentityInterface $user): GuardInterface;

    /**
     * @return int|string
     */
    public function id(): int|string;

    /**
     * @return string
     */
    public function getAuthName(): string;

    /**
     * @param string $authName
     * @return GuardInterface
     */
    public function setAuthName(string $authName): GuardInterface;

    /**
     * @param IdentityInterface $identity
     * @return mixed
     */
    public function login(IdentityInterface $identity): mixed;

    /**
     * @param mixed $params
     * @return bool
     */
    public function logout(mixed $params = null): bool;
}