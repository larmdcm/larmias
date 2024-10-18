<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Handler;

use Larmias\Contracts\Http\ResponseInterface;
use Larmias\HttpServer\Contracts\SseHandlerInterface;
use Stringable;

class SseHandler implements SseHandlerInterface
{
    protected bool $isEnd = false;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * @param string|Stringable $data
     * @return bool
     */
    public function write(string|Stringable $data): bool
    {
        $this->response->write((string)$data);

        return true;
    }

    /**
     * @param string $data
     * @return void
     */
    public function end(string $data = ''): void
    {
        $this->isEnd = true;
        $this->response->end($data);
    }

    public function isEnd(): bool
    {
        return $this->isEnd;
    }
}