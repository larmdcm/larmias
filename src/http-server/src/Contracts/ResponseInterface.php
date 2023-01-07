<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * @param array|object $data
     * @param int $code
     * @param array $headers
     * @return PsrResponseInterface
     */
    public function json(array|object $data, int $code = 200, array $headers = []): PsrResponseInterface;

    /**
     * @param string|\Stringable $data
     * @param int $code
     * @param array $headers
     * @return PsrResponseInterface
     */
    public function raw(string|\Stringable $data, int $code = 200, array $headers = []): PsrResponseInterface;
}