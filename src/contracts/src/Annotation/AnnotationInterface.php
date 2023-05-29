<?php

declare(strict_types=1);

namespace Larmias\Contracts\Annotation;

interface AnnotationInterface
{
    /**
     * @param string|array $annotations
     * @param string $handler
     * @return AnnotationInterface
     */
    public function addHandler(string|array $annotations, string $handler): AnnotationInterface;

    /**
     * @return void
     */
    public function scan(): void;
}