<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface FileInterface
{
    public function getFilename(): string;
}