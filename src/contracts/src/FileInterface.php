<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface FileInterface
{
    /**
     * @return string
     */
    public function getFilename(): string;
}