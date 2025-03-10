<?php

declare(strict_types=1);

namespace Larmias\FileWatcher\Driver;

use Larmias\Contracts\TimerInterface;
use Symfony\Component\Finder\Finder;
use SplFileInfo;
use function md5_file;
use function is_dir;
use function pathinfo;

class Scan extends AbstractDriver
{
    /**
     * @var Finder
     */
    protected Finder $finder;

    /**
     * @var SplFileInfo[]
     */
    protected array $files = [];

    /**
     * @var int
     */
    protected int $intervalTime;

    /**
     * @var TimerInterface
     */
    protected TimerInterface $timer;

    /**
     * @param TimerInterface $timer
     * @return void
     */
    public function initialize(TimerInterface $timer): void
    {
        $this->intervalTime = $this->config['scan_interval_time'] ?? 2000;
        $this->timer = $timer;
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void
    {
        $this->finder = $this->getFinder($this->includes, $this->excludes);
        $this->files = $this->getFiles();
        $this->timer->tick($this->intervalTime, function () use ($callback) {
            $files = $this->getFiles();
            $this->checkFiles($this->files, $files, $callback);
            $this->checkFiles($files, $this->files, function (string $path, int $event) use ($callback) {
                if ($event === self::EVENT_CREATE) {
                    $callback($path, self::EVENT_DELETE);
                }
            });
            $this->files = $files;
        });
    }

    /**
     * @param array $includes
     * @param array $excludes
     * @return Finder
     */
    protected function getFinder(array $includes = [], array $excludes = []): Finder
    {
        $name = [];
        $in = [];
        foreach ($includes as $path) {
            if (is_dir($path)) {
                $in[] = $path;
            } else {
                $info = pathinfo($path);
                if (is_dir($info['dirname'])) {
                    $in[] = $info['dirname'];
                }
                $name[] = $info['basename'];
            }
        }

        return Finder::create()->files()->name($name)->in($in)->exclude($excludes);
    }

    /**
     * @param array $files
     * @param array $newFiles
     * @param callable $callback
     * @return void
     */
    protected function checkFiles(array $files, array $newFiles, callable $callback): void
    {
        foreach ($newFiles as $path => $hash) {
            if (!isset($files[$path])) {
                $callback($path, self::EVENT_CREATE);
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
        /** @var SplFileInfo $file */
        foreach ($this->finder as $file) {
            $realPath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
            $files[$realPath] = md5_file($realPath);
        }
        return $files;
    }
}