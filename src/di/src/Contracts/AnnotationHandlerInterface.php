<?php

declare(strict_types=1);

namespace Larmias\Di\Contracts;

interface AnnotationHandlerInterface
{
    public function collect(array $param): void;

    public function handle(): void;
}