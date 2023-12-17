<?php

declare(strict_types=1);

namespace Larmias\Auth\Facade;

use Larmias\Auth\AuthManager;
use Larmias\Contracts\Auth\GuardInterface;
use Larmias\Contracts\Auth\IdentityInterface;
use Larmias\Contracts\ContextInterface;

/**
 * @method static bool attempt(array $params)
 * @method static bool check()
 * @method static bool guest()
 * @method static IdentityInterface user()
 * @method static int|string id()
 * @method static string getAuthName()
 * @method static GuardInterface setAuthName(string $authName)
 * @method static bool login(IdentityInterface $identity)
 * @method static bool logout()
 */
class Auth
{
    /**
     * @var ContextInterface
     */
    protected static ContextInterface $context;

    /**
     * @param ContextInterface $context
     * @return void
     */
    public static function setContext(ContextInterface $context): void
    {
        static::$context = $context;
    }

    /**
     * @param AuthManager $authManager
     * @return void
     */
    public static function setAuthManager(AuthManager $authManager): void
    {
        static::$context->set(static::getContextKey(), $authManager);
    }

    /**
     * @return bool
     */
    public static function hasAuthManager(): bool
    {
        return static::$context->has(static::getContextKey());
    }

    /**
     * @param string|null $name
     * @return GuardInterface
     */
    public static function guard(?string $name = null): GuardInterface
    {
        return static::$context->get(static::getContextKey())->guard($name);
    }

    /**
     * @return string
     */
    public static function getContextKey(): string
    {
        return 'auth.authManager';
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $name, array $args)
    {
        return static::guard()->{$name}(...$args);
    }
}