<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\FileInterface;
use Larmias\Contracts\Http\ResponseInterface;
use Larmias\Utils\Helper;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;

class ResponseEmitter implements ResponseEmitterInterface
{
    public function emit(PsrResponseInterface $response, ResponseInterface $serverResponse, bool $withContent = true): void
    {
        $content = $response->getBody();

        $serverResponse = $serverResponse->withHeaders($response->getHeaders())
            ->status($response->getStatusCode(), $response->getReasonPhrase());

        if ($content instanceof FileInterface) {
            $serverResponse->sendFile($content->getPathname());
            return;
        }

        if (\method_exists($response, 'getCookies')) {
            foreach ($response->getCookies() as $paths) {
                foreach ($paths ?? [] as $items) {
                    foreach ($items ?? [] as $cookie) {
                        if (Helper::isMethodsExists($cookie, [
                            'isRaw', 'getValue', 'getName', 'getExpiresTime', 'getPath', 'getDomain', 'isSecure', 'isHttpOnly', 'getSameSite',
                        ])) {
                            $value = $cookie->isRaw() ? $cookie->getValue() : \rawurlencode($cookie->getValue());
                            $serverResponse->cookie(
                                $cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(),
                                $cookie->isSecure(), $cookie->isHttpOnly(), $cookie->getSameSite()
                            );
                        }
                    }
                }
            }
        }

        if ($withContent) {
            $serverResponse->end((string)$content);
        } else {
            $serverResponse->end();
        }
    }
}