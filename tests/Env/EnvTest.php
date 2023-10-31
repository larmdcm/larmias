<?php

declare(strict_types=1);

namespace LarmiasTest\Env;

use Larmias\Context\ApplicationContext;
use Larmias\Contracts\DotEnvInterface;
use Larmias\Env\DotEnv;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function testGet(): void
    {
        $dotEnv = $this->getDotEnv();
        $this->assertSame($dotEnv->get('APP_NAME'), 'LARMIAS');
        $this->assertSame($dotEnv->get('APP_DEBUG'), true);
        $this->assertSame($dotEnv->get('APP_URL'), 'http://localhost');
        $this->assertSame($dotEnv->get('DB_PASSWORD'), '#/\a123d=!da&^*');
        $this->assertSame($dotEnv->get('REDIS_PASSWORD'), null);
        $this->assertSame($dotEnv->get('AWS_USE_PATH_STYLE_ENDPOINT'), false);
        $this->assertSame($dotEnv->get('DB_PORT'), '3306');
    }

    /**
     * @return DotEnvInterface
     * @throws \Throwable
     */
    public function getDotEnv(): DotEnvInterface
    {
        /** @var DotEnvInterface $dotEnv */
        $dotEnv = ApplicationContext::getContainer()->make(DotEnv::class, [], true);
        $dotEnv->load(__DIR__ . '/.env.example');
        return $dotEnv;
    }
}