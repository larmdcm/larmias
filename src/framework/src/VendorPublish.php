<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\VendorPublishInterface;
use Larmias\Utils\FileSystem;
use RuntimeException;
use function dirname;
use function str_replace;

class VendorPublish implements VendorPublishInterface
{
    /**
     * @var array
     */
    protected array $collect = [];

    /**
     * @param FileSystem $fileSystem
     */
    public function __construct(protected FileSystem $fileSystem)
    {
    }

    /**
     * @param string $name
     * @param array $paths
     * @return void
     */
    public function publishes(string $name, array $paths): void
    {
        $this->collect[$name] = $paths;
    }

    /**
     * @param string|null $name
     * @param bool $force
     * @return void
     */
    public function handle(?string $name = null, bool $force = false): void
    {
        $collect = $name ? [$this->collect[$name] ?? []] : $this->collect;

        foreach ($collect as $paths) {
            foreach ($paths as $source => $target) {
                if (!$this->fileSystem->isFile($source) && !$this->fileSystem->isDirectory($source)) {
                    throw new RuntimeException("Not a file or folder: {$source}");
                }

                $sourceIsFile = $this->fileSystem->isFile($source);
                $dir = $sourceIsFile ? dirname($target) : $target;
                $this->fileSystem->isDirectory($dir) || $this->makeDirectory($dir);

                if ($sourceIsFile) {
                    if (!$this->fileSystem->isFile($target) || $force) {
                        $this->fileSystem->copy($source, $target);
                    }
                } else {
                    $sourceDir = str_replace('\\', '/', realpath($source));
                    $targetDir = str_replace('\\', '/', $dir);
                    $files = $this->fileSystem->allFiles($sourceDir);
                    foreach ($files as $file) {
                        $sourceFile = str_replace('\\', '/', $file->getRealPath());
                        $basePath = str_replace($sourceDir . '/', '', $sourceFile);
                        $targetFile = $targetDir . '/' . $basePath;
                        if (!$this->fileSystem->isFile($targetFile) || $force) {
                            $this->fileSystem->isDirectory(dirname($targetFile)) || $this->makeDirectory(dirname($targetFile));
                            $this->fileSystem->copy($sourceFile, $targetFile);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $path
     * @return void
     */
    protected function makeDirectory(string $path): void
    {
        $this->fileSystem->makeDirectory($path, 0755, true);
    }
}