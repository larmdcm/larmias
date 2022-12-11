<?php

namespace Larmias\Engine\Watcher;

use Larmias\Engine\Contracts\WatcherInterface;
use Larmias\Engine\Timer;
use SplFileInfo;

class Scan implements WatcherInterface
{
    /**
     * @var string[]
     */
    protected array $path = [];

    /**
     * @var SplFileInfo[]
     */
    protected array $files = [];

    /**
     * @param string|array $path
     * @return WatcherInterface
     */
    public function add(string|array $path): WatcherInterface
    {
        if (\is_array($path)) {
            foreach ($path as $item) {
                $this->add($item);
            }
        } else {
            if (\is_file($path) || \is_dir($path)) {
                $this->path[] = $path;
            }
        }
        return $this;
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void
    {
        $this->files = $this->getFiles();
        Timer::tick(2,function () use ($callback) {
            $files = $this->getFiles();
            foreach ($files as $realpath => $mtime) {
                if (!isset($this->files[$realpath]) || $this->files[$realpath] != $mtime) {
                    $callback($realpath);
                    break;
                }
            }
            $this->files = $files;
        });
    }

    /**
     * @return array
     */
    protected function getFiles(): array
    {
        $files = [];
        foreach ($this->path as $path) {
            if (\is_dir($path)) {
                $files = \array_merge($files,$this->findFiles($path));
            } else {
                $files[] = new SplFileInfo($path);
            }
        }
        return array_column(
            array_map(fn(SplFileInfo $file) => ['realpath' => $file->getRealPath(),'mtime' => $file->getMTime()],$files),'mtime','realpath'
        );
    }

    /**
     * @param string $path
     * @return array
     */
    protected function findFiles(string $path): array
    {
        $files = [];
        $tmpFiles = scandir($path);
        foreach ($tmpFiles as $file) {
            if ($file == '..' || $file == '.') {
                continue;
            }
            $tmpFile = $path . '/' . $file;
            if (\is_dir($tmpFile)) {
                $files = \array_merge($files,$this->findFiles($tmpFile));
            } else {
                $files[] = new SplFileInfo($tmpFile);
            }
        }
        return $files;
    }
}