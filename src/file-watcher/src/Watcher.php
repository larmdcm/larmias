<?php

declare(strict_types=1);

namespace Larmias\FileWatcher;

use Larmias\Contracts\ContainerInterface;
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
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        /** @var FileWatcherInterface $driver */
        $driver = $this->container->make($this->config['driver'], ['config' => $this->config]);
        $this->driver = $driver;
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