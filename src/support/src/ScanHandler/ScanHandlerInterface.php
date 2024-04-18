<?php

declare(strict_types=1);

namespace Larmias\Support\ScanHandler;

interface ScanHandlerInterface
{
    /**
     * @return Scanned
     */
    public function scan(): Scanned;
}