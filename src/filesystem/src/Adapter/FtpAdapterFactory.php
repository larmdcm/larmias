<?php

declare(strict_types=1);

namespace Larmias\FileSystem\Adapter;

use Larmias\FileSystem\Contracts\AdapterFactoryInterface;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Ftp\ConnectivityCheckerThatCanFail;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Ftp\NoopCommandConnectivityChecker;

class FtpAdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options): FilesystemAdapter
    {
        $options = FtpConnectionOptions::fromArray($options);

        $connectivityChecker = new ConnectivityCheckerThatCanFail(new NoopCommandConnectivityChecker());

        return new FtpAdapter($options, null, $connectivityChecker);
    }
}