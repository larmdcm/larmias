<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * @param array|object $data
     * @return PsrResponseInterface
     */
    public function json(array|object $data): PsrResponseInterface;

    /**
     * @param string|\Stringable $data
     * @return PsrResponseInterface
     */
    public function raw(string|\Stringable $data): PsrResponseInterface;
}