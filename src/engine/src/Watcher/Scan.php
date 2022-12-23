<?php

declare(strict_types=1);

namespace Larmias\Engine\Watcher;

use Larmias\Engine\Timer;
use Larmias\Utils\Arr;
use RecursiveIterator;
use SplFileInfo;
use Larmias\Engine\Contracts\WatcherInterface;

class Scan implements WatcherInterface
{
    /**
     * @var array|array[]
     */
    protected array $config = [
        'include' => [],
        'exclude' => [],
        'excludeExt' => [],
    ];

    /**
     * @var SplFileInfo[]
     */
    protected array $files = [];

    /**
     * @var int
     */
    protected int $intervalTime = 2000;

    /**
     * @param string|array $path
     * @return WatcherInterface
     */
    public function include(string|array $path): WatcherInterface
    {
        return $this->setConfig(__FUNCTION__, $path);
    }

    /**
     * @param string|array $path
     * @return \Larmias\Engine\Contracts\WatcherInterface
     */
    public function exclude(string|array $path): WatcherInterface
    {
        return $this->setConfig(__FUNCTION__, $path);
    }

    /**
     * @param array|string $ext
     * @return \Larmias\Engine\Contracts\WatcherInterface
     */
    public function excludeExt(array|string $ext): WatcherInterface
    {
        return $this->setConfig(__FUNCTION__, $ext);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    protected function setConfig(string $name, mixed $value): self
    {
        if (\is_array($this->config[$name])) {
            $this->config[$name] = \array_merge($this->config[$name], Arr::wrap($value));
        } else {
            $this->config[$name] = $value;
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
        Timer::tick($this->intervalTime, function () use ($callback) {
            $files = $this->getFiles();
            $this->checkFiles($this->files, $files, $callback);
            $this->checkFiles($files, $this->files, function (string $path, int $event) use ($callback) {
                if ($event === self::EVENT_ADD) {
                    $callback($path, self::EVENT_DELETE);
                }
            });
            $this->files = $files;
        });
    }

    /**
     * @param array $files
     * @param array $newFiles
     * @param callable $callback
     */
    protected function checkFiles(array $files, array $newFiles, callable $callback)
    {
        foreach ($newFiles as $path => $hash) {
            if (!isset($files[$path])) {
                $callback($path, self::EVENT_ADD);
            } else if ($files[$path] !== $hash) {
                $callback($path, self::EVENT_UPDATE);
            }
        }
    }

    /**
     * @return array
     */
    protected function getFiles(): array
    {
        $files = [];
        foreach ($this->config['include'] as $path) {
            if (\is_dir($path)) {
                $files = \array_merge($files, $this->findFiles($path));
            } else {
                $files[] = $path;
            }
        }
        return array_column(
            array_map(fn(string $path) => ['path' => $path, 'hash' => \md5(file_get_contents($path))], $files), 'hash', 'path'
        );
    }

    /**
     * @param string $path
     * @return array
     */
    protected function findFiles(string $path): array
    {
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator(new class($directory, $this->config) extends \RecursiveFilterIterator {
            public function __construct(RecursiveIterator $iterator, protected array $config)
            {
                parent::__construct($iterator);
            }

            public function accept(): bool|int
            {
                if ($this->current()->isDir()) {
                    return !\preg_match('/^\./', $this->current()->getFilename()) && !\in_array($this->current()->getFilename(), $this->config['exclude']);
                }
                $list = \array_map(fn($item) => "\.$item", $this->config['excludeExt']);
                $list = \implode('|', $list);
                return \preg_match("/($list)$/", $this->current()->getFilename());
            }
        });
        return \array_map(fn($fileInfo) => $fileInfo->getPathname(), iterator_to_array($iterator));
    }
}