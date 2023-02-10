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
     * @return bool
     */
    public function logout(): bool;
}