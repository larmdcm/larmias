<?php

declare(strict_types=1);

namespace Larmias\Cache\Driver;

use Throwable;
use FilesystemIterator;
use function is_null;
use function is_file;
use function dirname;
use function is_dir;
use function mkdir;
use function time;
use function file_put_contents;
use function clearstatcache;
use function rmdir;
use function hash;
use function file_get_contents;
use function sys_get_temp_dir;
use function unlink;
use const DIRECTORY_SEPARATOR;

class File extends Driver
{
    /**
     * @var array
     */
    protected array $config = [
        'expire' => 0,
        'path' => null,
        'prefix' => '',
        'cache_sub_dir' => false,
        'hash_type' => 'md5',
    ];

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        $cache = $this->getCache($key);
        return is_null($cache) ? $default : $cache['data'];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|\DateTimeInterface|\DateInterval $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool
    {
        $file = $this->getCacheKey($key);
        if (!is_file($file)) {
            $dirname = dirname($file);
            is_dir($dirname) || mkdir($dirname, 0755, true);
        }
        if (is_null($ttl)) {
            $ttl = $this->config['expire'];
        }
        $cache['create_time'] = time();
        $cache['expire_time'] = $this->getExpireTime($ttl);
        $cache['data'] = $value;
        file_put_contents($file, $this->packer->pack($cache));
        clearstatcache();
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key): bool
    {
        $file = $this->getCacheKey($key);
        return $this->unlink($file);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $dirname = $this->getPath() . DIRECTORY_SEPARATOR . $this->config['prefix'];
        $result = $this->rmDir($dirname);
        clearstatcache();
        return $result;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key): bool
    {
        return is_file($this->getCacheKey($key));
    }

    /**
     * @param string $key
     * @param int $step
     * @return int|null
     */
    public function increment(string $key, int $step = 1): ?int
    {
        $cache = $this->getCache($key);
        [$value, $expire] = $cache ? [$cache['data'], $cache['expire_time']] : [0, 0];
        $value += $step;
        return $this->set($key, $value, $expire) ? $value : null;
    }

    /**
     * @param string $key
     * @param int $step
     * @return int|null
     */
    public function decrement(string $key, int $step = 1): ?int
    {
        return $this->increment($key, -$step);
    }

    /**
     * 获取缓存文件路径
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        if ($this->config['hash_type']) {
            $key = hash($this->config['hash_type'], $key);
        }

        if ($this->config['cache_sub_dir']) {
            $key = substr($key, 0, 2) . DIRECTORY_SEPARATOR . $key;
        }

        if ($this->config['prefix']) {
            $key = $this->config['prefix'] . DIRECTORY_SEPARATOR . $key;
        }

        return $this->getPath() . DIRECTORY_SEPARATOR . $key;
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        if (is_null($this->config['path'])) {
            $this->config['path'] = sys_get_temp_dir();
        }
        return $this->config['path'];
    }

    /**
     * 获取缓存数据
     * @param string $key
     * @return mixed
     */
    protected function getCache(string $key): mixed
    {
        $file = $this->getCacheKey($key);
        if (!is_file($file)) {
            return null;
        }
        $cache = $this->packer->unpack(file_get_contents($file));
        if ($cache['expire_time'] != 0) {
            $expireTime = $cache['create_time'] + $cache['expire_time'];
            if (time() > $expireTime) {
                $dirname = dirname($file);
                $this->unlink($file);
                if ($this->config['cache_sub_dir'] && is_dir($dirname)) {
                    rmdir($dirname);
                }
                clearstatcache();
                return null;
            }
        }
        return $cache;
    }


    /**
     * 判断文件是否存在后，删除
     * @param string $path
     * @return bool
     */
    protected function unlink(string $path): bool
    {
        try {
            return is_file($path) && unlink($path);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 删除文件夹
     * @param $dirname
     * @return bool
     */
    protected function rmdir($dirname): bool
    {
        if (!is_dir($dirname)) {
            return false;
        }

        $items = new FilesystemIterator($dirname);

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->rmdir($item->getPathname());
            } else {
                $this->unlink($item->getPathname());
            }
        }

        rmdir($dirname);

        return true;
    }
}