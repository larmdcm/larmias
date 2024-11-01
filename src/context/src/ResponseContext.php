<?php

declare(strict_types=1);

namespace Larmias\Context;

use Psr\Http\Message\ResponseInterface;

class ResponseContext
{
    /**
     * @param int|null $cid
     * @return ResponseInterface
     */
    public static function get(?int $cid = null): ResponseInterface
    {
        return Context::get(ResponseInterface::class, null, $cid);
    }

    /**
     * @param ResponseInterface $request
     * @param int|null $cid
     * @return ResponseInterface
     */
    public static function set(ResponseInterface $request, ?int $cid = null): ResponseInterface
    {
        return Context::set(ResponseInterface::class, $request, $cid);
    }

    /**
     * @param int|null $cid
     * @return bool
     */
    public function has(?int $cid = null): bool
    {
        return Context::has(ResponseInterface::class, $cid);
    }
}