<?php

declare(strict_types=1);

namespace LarmiasTest\JWT;

use Larmias\Cache\Providers\CacheServiceProvider;
use Larmias\Context\ApplicationContext;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\DotEnvInterface;
use Larmias\Env\DotEnv;
use Larmias\JWTAuth\Contracts\BlacklistInterface;
use Larmias\JWTAuth\Contracts\JWTInterface;
use Larmias\JWTAuth\Providers\JWTAuthServiceProvider;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function setUp(): void
    {
        $container = ApplicationContext::getContainer();
        $container->bindIf(DotEnvInterface::class, DotEnv::class);
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);
        $config->load(LARMIAS_BASE_PATH . '/jwt-auth/publish/jwt.php');
        $config->load(LARMIAS_BASE_PATH . '/cache/publish/cache.php');
        $services = [
            CacheServiceProvider::class,
            JWTAuthServiceProvider::class,
        ];

        foreach ($services as $service) {
            $serviceProvider = new $service($container);
            $serviceProvider->register();
            $serviceProvider->boot();
        }
    }

    public function getJWT(string $scene = 'default'): JWTInterface
    {
        return ApplicationContext::getContainer()->get(JWTInterface::class)->setScene($scene);
    }

    public function getBlacklist(): BlacklistInterface
    {
        return ApplicationContext::getContainer()->get(BlacklistInterface::class);
    }
}