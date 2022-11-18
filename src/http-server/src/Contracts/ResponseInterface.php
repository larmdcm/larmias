<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

interface ResponseInterface
{
    public function json(mixed $data): ResponseInterface;

    public function raw(mixed $data): ResponseInterface;

    public function send(): void;
}