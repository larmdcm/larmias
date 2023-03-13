<?php

declare(strict_types=1);

namespace Larmias\FileWatcher;

use Larmias\Contracts\FileWatcherInterface;
use Larmias\FileWatcher\Drivers\Scan;
use function array_merge;

class Watcher implements FileWatcherInterface
{
    /**
     * @var FileWatcherInterface
     */
    protected FileWatcherInterface $driver;

    /**
     * @var array
     */
    protected array $config = [
        'driver' => Scan::class,
        'includes' => [],
        'excludes' => [],
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->driver = new $this->config['driver']($this->config);
    }

    /**
     * @param array $config
     * @return FileWatcherInterface
     */
    public static function create(array $config = []): FileWatcherInterface
    {
        return new static($config);
    }

    /**
     * @param array|string $path
     * @return FileWatcherInterface
     */
    public function include(array|string $path): FileWatcherInterface
    {
        return $this->driver->include($path);
    }

    /**
     * @param array|string $path
     * @return FileWatcherInterface
     */
    public function exclude(array|string $path): FileWatcherInterface
    {
        return $this->driver->include($path);
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void
    {
        $this->driver->watch($callback);
    }
}