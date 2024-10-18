<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

interface SseResponseInterface
{
    /**
     * @return SseEmitterInterface|null
     */
    public function getSseEmitter(): ?SseEmitterInterface;
}