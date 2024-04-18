<?php

declare(strict_types=1);

namespace Larmias\Support\ScanHandler;

use RuntimeException;

class PcntlScanHandler implements ScanHandlerInterface
{
    public function __construct()
    {
        if (!extension_loaded('pcntl')) {
            throw new RuntimeException('Missing pcntl extension.');
        }
    }

    public function scan(): Scanned
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new RuntimeException('The process fork failed');
        }
        if ($pid) {
            pcntl_wait($status, WUNTRACED);
            return new Scanned(true);
        }

        return new Scanned(false);
    }
}