<?php

declare(strict_types=1);

namespace Larmias\Session\Handler;

use FilesystemIterator;
use SplFileInfo;
use Generator;
use Closure;
use Throwable;
use function sys_get_temp_dir;
use function rtrim;
use function is_dir;
use function mkdir;
use function random_int;
use function time;
use function is_file;
use function filemtime;
use function function_exists;
use function unlink;
use function dirname;
use function fopen;
use function flock;
use function clearstatcache;
use function fread;
use function filesize;
use function fclose;
use function file_put_contents;
use const LOCK_UN;
use const LOCK_EX;

class FileHandler extends Driver
{
    /**
     * @var array
     */
    protected array $config = [
        'path' => '',
        'expire' => 1440,
        'prefix' => '',
        'data_compress' => false,
        'gc_probability' => 1,
        'gc_divisor' => 100,
    ];

    /**
     * @return void
     * @throws Throwable
     */
    public function initialize(): void
    {
        if (empty($this->config['path'])) {
            $this->config['path'] = sys_get_temp_dir();
        }
        $this->config['path'] = rtrim($this->config['path'], DIRECTORY_SEPARATOR);

        try {
            !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);
        } catch (Throwable) {
        }

        if (random_int(1, $this->config['gc_divisor']) <= $this->config['gc_probability']) {
            $this->gc($this->config['expire']);
        }
    }

    /**
     * @see https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return mixed
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @see https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        try {
            return $this->unlink($this->getFileName($id));
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $max_lifetime
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {
        $now = time();

        $files = $this->findFiles($this->config['path'], function (SplFileInfo $item) use ($max_lifetime, $now) {
            return $now - $max_lifetime > $item->getMTime();
        });

        foreach ($files as $file) {
            $this->unlink($file->getPathname());
        }

        return 0;
    }

    /**
     * Initialize session.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $path the path where to store/retrieve the session
     * @param string $name the session name
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $id the session id to read data for
     * @return string
     */
    public function read(string $id): string
    {
        $filename = $this->getFileName($id);

        if (is_file($filename) && filemtime($filename) >= time() - $this->config['expire']) {
            $content = $this->readFile($filename);

            if ($this->config['data_compress'] && function_exists('gzuncompress')) {
                //启用数据压缩
                $content = (string)gzuncompress($content);
            }

            return $content;
        }

        return '';
    }

    /**
     * Write session data.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $id
     * @param string $data
     */
    public function write(string $id, string $data): bool
    {
        $filename = $this->getFileName($id, true);

        if ($this->config['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }

        return $this->writeFile($filename, $data);
    }

    /**
     * 查找文件
     * @param string $root
     * @param Closure $filter
     * @return Generator
     */
    protected function findFiles(string $root, Closure $filter): Generator
    {
        $items = new FilesystemIterator($root);

        /** @var SplFileInfo $item */
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                yield from $this->findFiles($item->getPathname(), $filter);
            } else {
                if ($filter($item)) {
                    yield $item;
                }
            }
        }
    }

    /**
     * 取得变量的存储文件名
     * @param string $name
     * @param bool $auto
     * @return string
     */
    protected function getFileName(string $name, bool $auto = false): string
    {
        if ($this->config['prefix']) {
            $name = $this->config['prefix'] . DIRECTORY_SEPARATOR . 'sess_' . $name;
        } else {
            $name = 'sess_' . $name;
        }

        $filename = $this->config['path'] . DIRECTORY_SEPARATOR . $name;
        $dir = dirname($filename);

        if ($auto && !is_dir($dir)) {
            try {
                mkdir($dir, 0755, true);
            } catch (Throwable) {
            }
        }

        return $filename;
    }

    /**
     * 读取文件内容
     * @param string $path
     * @return string
     */
    protected function readFile(string $path): string
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, filesize($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * 写文件（加锁）
     * @param $path
     * @param $content
     * @return bool
     */
    protected function writeFile($path, $content): bool
    {
        return (bool)file_put_contents($path, $content, LOCK_EX);
    }

    /**
     * 判断文件是否存在后，删除
     *
     * @param string $file
     * @return bool
     */
    private function unlink(string $file): bool
    {
        return is_file($file) && unlink($file);
    }
}