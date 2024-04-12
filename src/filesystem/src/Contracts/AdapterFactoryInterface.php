<?php

declare(strict_types=1);

namespace Larmias\FileSystem\Contracts;

use League\Flysystem\FilesystemAdapter;

interface AdapterFactoryInterface
{
    /**
     * @param array $options
     * @return FilesystemAdapter
     */
    public function make(array $options): FilesystemAdapter;
}