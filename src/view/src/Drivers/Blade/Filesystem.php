<?php

declare(strict_types=1);

namespace Larmias\View\Drivers\Blade;

use Exception;

class Filesystem
{
    /**
     * Determine if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @return string
     *
     * @throws Exception
     */
    public function get(string $path): string
    {
        if ($this->isFile($path)) {
            return file_get_contents($path);
        }

        throw new Exception("File does not exist at path {$path}");
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param bool $lock
     * @return int|false
     */
    public function put(string $path, string $contents, bool $lock = false): int|false
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     * @return int
     */
    public function lastModified(string $path): int
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param string $file
     * @return bool
     */
    public function isFile(string $file): bool
    {
        return is_file($file);
    }
}
