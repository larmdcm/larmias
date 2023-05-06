<?php

declare(strict_types=1);

namespace Larmias\FileWatcher\Drivers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\FileWatcherInterface;
use function method_exists;
use function array_merge;

abstract class Driver implements FileWatcherInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var array
     */
    protected array $includes = [];

    /**
     * @var array
     */
    protected array $excludes = [];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->includes = $this->config['includes'] ?? [];
        $this->excludes = $this->config['excludes'] ?? [];

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @param string|array $path
     * @return FileWatcherInterface
     */
    public function include(string|array $path): FileWatcherInterface
    {
        $this->includes = array_merge($this->includes, (array)$path);
        return $this;
    }

    /**
     * @param string|array $path
     * @return FileWatcherInterface
     */
    public function exclude(string|array $path): FileWatcherInterface
    {
        $this->excludes = array_merge($this->excludes, (array)$path);
        return $this;
    }
}