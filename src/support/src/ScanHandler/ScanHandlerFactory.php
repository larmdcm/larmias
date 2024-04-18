<?php

declare(strict_types=1);

namespace Larmias\Support\ScanHandler;

class ScanHandlerFactory
{
    public function make(): ScanHandlerInterface
    {
        return extension_loaded('pcntl') ? new PcntlScanHandler() : new ProcScanHandler();
    }
}