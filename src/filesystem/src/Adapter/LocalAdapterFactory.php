<?php

declare(strict_types=1);

namespace Larmias\FileSystem\Adapter;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\FilesystemAdapter;

class LocalAdapterFactory
{
    /**
     * @param array $options
     * @return FilesystemAdapter
     */
    public function make(array $options): FilesystemAdapter
    {
        return new LocalFilesystemAdapter($options['root']);
    }
}