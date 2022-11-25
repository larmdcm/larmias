<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Http\Message\Stream;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Contracts\Http\ResponseInterface as ServerResponseInterface;
use Larmias\Utils\Codec\Json;
use Larmias\Http\Message\Response as ServerResponse;

class Response extends ServerResponse implements ResponseInterface
{
    public function __construct(protected ServerResponseInterface $serverResponse)
    {
        parent::__construct();
    }

    public function json(mixed $data): ResponseInterface
    {
        $json = Json::encode($data);
        return $this->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(Stream::create($json));
    }

    public function raw(mixed $data): ResponseInterface
    {
        return $this->withAddedHeader('content-type', 'text/plain; charset=utf-8')
            ->withBody(Stream::create((string)$data));
    }

    public function send(): void
    {
        $this->serverResponse
            ->withHeaders($this->getHeaders())
            ->status($this->getStatusCode(), $this->getReasonPhrase())
            ->end((string)$this->getBody());
    }
}