<?php

declare(strict_types=1);

namespace Larmias\Context;

use Psr\Http\Message\ServerRequestInterface;

class RequestContext
{
    /**
     * @param int|null $cid
     * @return ServerRequestInterface
     */
    public static function get(?int $cid = null): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class, null, $cid);
    }

    /**
     * @param ServerRequestInterface $request
     * @param int|null $cid
     * @return ServerRequestInterface
     */
    public static function set(ServerRequestInterface $request, ?int $cid = null): ServerRequestInterface
    {
        return Context::set(ServerRequestInterface::class, $request, $cid);
    }

    /**
     * @param int|null $cid
     * @return bool
     */
    public static function has(?int $cid = null): bool
    {
        return Context::has(ServerRequestInterface::class, $cid);
    }
}