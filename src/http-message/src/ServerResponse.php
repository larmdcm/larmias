<?php

declare(strict_types=1);

namespace Larmias\Http\Message;

use Larmias\Contracts\Http\ResponseInterface;
use Larmias\Http\Message\Contracts\Chunkable;

class ServerResponse extends Response implements Chunkable
{
    /** @var array */
    protected array $cookies = [];

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $rawResponse;

    /**
     * @param Cookie $cookie
     * @return self
     */
    public function withCookie(Cookie $cookie): self
    {
        $new = clone $this;
        $new->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $new;
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool
    {
        return $this->rawResponse->write($data);
    }

    /**
     * @return ResponseInterface
     */
    public function getRawResponse(): ResponseInterface
    {
        return $this->rawResponse;
    }

    /**
     * @param ResponseInterface $rawResponse
     */
    public function setRawResponse(ResponseInterface $rawResponse): void
    {
        $this->rawResponse = $rawResponse;
    }
}