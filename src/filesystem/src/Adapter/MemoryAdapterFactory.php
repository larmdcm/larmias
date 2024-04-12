<?php

declare(strict_types=1);

namespace Larmias\FileSystem\Adapter;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

class MemoryAdapterFactory
{
    /**
     * @param array $options
     * @return FilesystemAdapter
     */
    public function make(array $options): FilesystemAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}