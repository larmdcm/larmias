<?php

declare(strict_types=1);

namespace Larmias\Di\Contracts;

interface AnnotationInterface
{
    public function addHandler(string|array $annotations, string $handler): AnnotationInterface;

    public function scan(): void;
}