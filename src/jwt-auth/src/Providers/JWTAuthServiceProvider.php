<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Providers;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\JWTAuth\BlackList;
use Larmias\JWTAuth\Contracts\BlacklistInterface;
use Larmias\JWTAuth\Contracts\JWTInterface;
use Larmias\JWTAuth\Contracts\ParserInterface;
use Larmias\JWTAuth\JWT;
use Larmias\JWTAuth\Parser\AuthHeaderParser;

class JWTAuthServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

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
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(id: VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/jwt.php' => $app->getConfigPath() . 'jwt.php',
            ]);
        }
    }
}