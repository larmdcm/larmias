<?php

declare(strict_types=1);

namespace LarmiasTest\JWT;

use Larmias\Cache\Providers\CacheServiceProvider;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\DotEnvInterface;
use Larmias\Env\DotEnv;
use Larmias\JWTAuth\Contracts\JWTInterface;
use Larmias\JWTAuth\JWT;
use Larmias\JWTAuth\Providers\JWTAuthServiceProvider;
use Larmias\Utils\ApplicationContext;
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

    public function getJWT(string $scene = 'default'): JWT
    {
        return ApplicationContext::getContainer()->get(JWTInterface::class)->setScene($scene);
    }
}