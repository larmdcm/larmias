<?php

declare(strict_types=1);

namespace Larmias\FileSystem;

use InvalidArgumentException;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\FileSystem\Adapter\LocalAdapterFactory;
use Larmias\FileSystem\Contracts\AdapterFactoryInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use Throwable;

class FileSystemFactory
{
    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * @param string|null $name
     * @return Filesystem
     * @throws Throwable
     */
    public function get(?string $name = null): Filesystem
    {
        $options = $this->config->get('file', [
            'default' => 'local',
            'storage' => [
                'local' => [
                    'driver' => LocalAdapterFactory::class,
                    'root' => sys_get_temp_dir(),
                ],
            ],
        ]);

        $name = $name ?: $options['default'];

        $adapter = $this->getAdapter($options, $name);
        return new Filesystem($adapter, $options['storage'][$name] ?? []);
    }

    /**
     * @param array $options
     * @param string $name
     * @return FilesystemAdapter
     * @throws Throwable
     */
    public function getAdapter(array $options, string $name): FilesystemAdapter
    {
        if (!$options['storage'] || !$options['storage'][$name]) {
            throw new InvalidArgumentException("file configurations are missing {$name} options");
        }
        /** @var AdapterFactoryInterface $driver */
        $driver = $this->container->get($options['storage'][$name]['driver']);
        return $driver->make($options['storage'][$name]);
    }
}