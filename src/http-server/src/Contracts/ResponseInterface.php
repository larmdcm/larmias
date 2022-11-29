<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

interface ResponseInterface
{
    /**
     * @param array|object $data
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function json(array|object $data): ResponseInterface;

    /**
     * @param string|\Stringable $data
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function raw(string|\Stringable $data): ResponseInterface;

    /**
     * @return void
     */
    public function send(): void;
}