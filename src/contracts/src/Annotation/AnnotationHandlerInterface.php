<?php

declare(strict_types=1);

namespace Larmias\Contracts\Annotation;

interface AnnotationHandlerInterface
{
    /**
     * @param array $param
     * @return void
     */
    public function collect(array $param): void;

    /**
     * @return void
     */
    public function handle(): void;
}