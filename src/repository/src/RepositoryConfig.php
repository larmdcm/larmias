<?php

declare(strict_types=1);

namespace Larmias\Repository;

class RepositoryConfig
{
    /**
     * @var array
     */
    protected static array $defaultConfig = [
        'default' => 'think',
        'drivers' => [
            'think' => [
                'driver' => \Larmias\Repository\Drivers\Think\EloquentDriver::class,
                'query' => \Larmias\Repository\Drivers\Think\EloquentQueryRelate::class,
            ]
        ]
    ];

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param array $config
     * @param string|null $name
     */
    public function __construct(array $config = [], protected ?string $name = null)
    {
        $this->merge($config);
    }

    /**
     * make RepositoryConfig.
     *
     * @param array $config
     * @param string|null $name
     * @return RepositoryConfig
     */
    public static function make(array $config = [], ?string $name = null): RepositoryConfig
    {
        return new static($config, $name);
    }

    /**
     * config get.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null)
    {
        return data_get($this->all(), $name, $default);
    }

    /**
     * config set.
     *
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function set(string $name, $value): self
    {
        data_set($this->config, $name, $value);
        return $this;
    }

    /**
     * 获取全部配置
     *
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * 合并配置
     *
     * @param array $config
     * @return RepositoryConfig
     */
    public function merge(array $config): self
    {
        $config = \array_merge($config, static::$defaultConfig);
        $this->config = $config['drivers'][$this->name ?: $config['default']] ?? [];
        return $this;
    }

    /**
     * @param array $config
     * @return void
     */
    public function setDefaultConfig(array $config): void
    {
        static::$defaultConfig = \array_merge(static::$defaultConfig, $config);
    }
}