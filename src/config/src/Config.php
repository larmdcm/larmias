<?php

declare(strict_types=1);

namespace Larmias\Config;

use Larmias\Contracts\ConfigInterface;
use Larmias\Utils\Arr;
use ArrayAccess;
use function is_array;
use function pathinfo;
use function parse_ini_file;
use function json_decode;
use function function_exists;
use function file_get_contents;
use const INI_SCANNER_TYPED;

class Config implements ArrayAccess, ConfigInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * Config __construct
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 加载文件配置
     *
     * @param string $file
     * @param string|null $key
     * @return array
     */
    public function load(string $file, ?string $key = null): array
    {
        $info = pathinfo($file);
        $config = [];
        switch ($info['extension']) {
            case 'php':
                $config = include $file;
                break;
            case 'yml':
            case 'yaml':
                if (function_exists('yaml_parse_file')) {
                    $config = yaml_parse_file($file);
                }
                break;
            case 'ini':
                $config = parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
                break;
            case 'json':
                $config = json_decode(file_get_contents($file), true);
                break;
        }
        $key = $key ?: $info['filename'];
        return $this->set($key, $config);
    }

    /**
     * 获取配置是否存在
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return Arr::has($this->config, $key);
    }

    /**
     * 设置配置参数.
     *
     * @param string|array $key
     * @param mixed $value
     * @return array
     */
    public function set(string|array $key, mixed $value = null): array
    {
        if (is_array($key)) {
            foreach ($key as $itemKey => $itemValue) {
                Arr::set($this->config, $itemKey, $itemValue);
            }
        } else {
            Arr::set($this->config, $key, $value);
        }
        return $this->config;
    }

    /**
     * 获取配置参数
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * 数组配置追加
     *
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function push(string $key, mixed $value): array
    {
        $array = $this->get($key, []);
        $array[] = $value;
        return $this->set($key, $array);
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
     * Determine if the given configuration option exists.
     *
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get a configuration option.
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set a configuration option.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param mixed $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset);
    }
}