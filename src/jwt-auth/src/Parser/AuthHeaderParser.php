<?php

declare(strict_types=1);

namespace Larmias\JWTAuth\Parser;

use Larmias\Contracts\ContextInterface;
use Larmias\JWTAuth\Contracts\ParserInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthHeaderParser implements ParserInterface
{
    /**
     * @var string
     */
    protected string $headerName = 'authorization';

    /**
     * @var string
     */
    protected string $prefix = 'bearer';

    /**
     * @param ContextInterface $context
     */
    public function __construct(protected ContextInterface $context)
    {
    }


    /**
     * @return string
     */
    public function parse(): string
    {
        /** @var ServerRequestInterface $request */
        $request = $this->context->get(ServerRequestInterface::class);
        $header = $request->getHeaderLine($this->headerName);
        return $header && preg_match('/' . $this->prefix . '\s*(\S+)\b/i', $header, $matches) ? $matches[1] : '';
    }

    /**
     * @param string $name
     * @return self
     */
    public function setHeaderName(string $name): self
    {
        $this->headerName = $name;

        return $this;
    }

    /**
     * @param string $prefix
     * @return self
     */
    public function setHeaderPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}