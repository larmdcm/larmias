<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Providers;

use Larmias\JWTAuth\BlackList;
use Larmias\JWTAuth\Contracts\BlacklistInterface;
use Larmias\JWTAuth\Contracts\JWTInterface;
use Larmias\JWTAuth\Contracts\ParserInterface;
use Larmias\JWTAuth\JWT;
use Larmias\JWTAuth\Parser\AuthHeaderParser;
use Larmias\Framework\ServiceProvider;

class JWTAuthServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            BlacklistInterface::class => BlackList::class,
            JWTInterface::class => JWT::class,
            ParserInterface::class => AuthHeaderParser::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/jwt.php' => $this->app->getConfigPath() . 'jwt.php',
        ]);
    }
}