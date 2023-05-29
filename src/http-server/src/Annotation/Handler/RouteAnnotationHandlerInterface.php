<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation\Handler;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;

interface RouteAnnotationHandlerInterface extends AnnotationHandlerInterface
{
    /**
     * @param string $prefix
     * @return string
     */
    public function buildControllerPrefix(string $prefix): string;
}