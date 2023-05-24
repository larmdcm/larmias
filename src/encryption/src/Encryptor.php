<?php

declare(strict_types=1);

namespace Larmias\Encryption;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Encryption\EncryptorInterface;
use function is_null;

class Encryptor implements EncryptorInterface
{
    /**
     * @var EncryptorInterface[]
     */
    protected array $drivers = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * 获取存储驱动
     *
     * @param string|null $name
     * @return EncryptorInterface
     */
    public function driver(?string $name = null): EncryptorInterface
    {
        $name = $name ?: $this->getConfig('default');
        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }
        $driverConfig = $this->getConfig('handlers.' . $name);
        /** @var EncryptorInterface $store */
        $store = $this->container->make($driverConfig['driver'], ['config' => $driverConfig]);
        return $this->drivers[$name] = $store;
    }

    /**
     * 获取配置.
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if (is_null($name)) {
            return $this->config->get('encryption');
        }
        return $this->config->get('encryption.' . $name, $default);
    }

    /**
     * @param string $data
     * @param array $params
     * @return string
     */
    public function encrypt(string $data, array $params = []): string
    {
        return $this->driver()->encrypt($data, $params);
    }

    /**
     * @param string $data
     * @param array $params
     * @return string
     */
    public function decrypt(string $data, array $params = []): string
    {
        return $this->driver()->decrypt($data, $params);
    }

    /**
     * @param int $length
     * @return string
     */
    public function generateKey(int $length = 32): string
    {
        return $this->driver()->generateKey($length);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->driver()->getKey();
    }
}