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
    /**
     * Response constructor.
     * @param \Larmias\Contracts\Http\ResponseInterface $serverResponse
     */
    public function __construct(protected ServerResponseInterface $serverResponse)
    {
        parent::__construct();
    }

    /**
     * @param array|object $data
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function json(array|object $data): ResponseInterface
    {
        $json = Json::encode($data);
        return $this->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(Stream::create($json));
    }

    /**
     * @param string|\Stringable $data
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function raw(string|\Stringable $data): ResponseInterface
    {
        return $this->withAddedHeader('content-type', 'text/plain; charset=utf-8')
            ->withBody(Stream::create((string)$data));
    }

    /**
     * @return void
     */
    public function send(): void
    {
        $this->serverResponse
            ->withHeaders($this->getHeaders())
            ->status($this->getStatusCode(), $this->getReasonPhrase())
            ->end((string)$this->getBody());
    }
}