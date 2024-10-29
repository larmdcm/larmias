<?php

declare(strict_types=1);

namespace Larmias\Support;

class FileLock
{
    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var string
     */
    const SUFFIX = '.lock';

    /**
     * @param string $name
     * @param string|null $path
     */
    public function __construct(string $name = 'lock', ?string $path = null)
    {
        $path = $path ?: sys_get_temp_dir();
        $this->filename = $path . ltrim(str_replace('\\', '_', $name), '/') . self::SUFFIX;
        if (!is_file($this->filename)) {
            touch($this->filename);
        }
    }

    /**
     * @param string $name
     * @param string|null $path
     * @return FileLock
     */
    public static function create(string $name = 'lock', ?string $path = null): FileLock
    {
        return new static($name, $path);
    }

    /**
     * @param callable $resolve
     * @param boolean $block
     * @return mixed
     */
    public function tryLock(callable $resolve, bool $block = true): mixed
    {
        $result = null;
        $fd = fopen($this->filename, 'r');
        if (!is_resource($fd)) {
            return null;
        }
        try {
            $flag = $block ? LOCK_EX : LOCK_EX | LOCK_NB;
            $getLock = flock($fd, $flag);
            if ($getLock) {
                $result = call_user_func($resolve);
            }
        } finally {
            flock($fd, LOCK_UN);
            fclose($fd);
        }
        return $result;
    }
}