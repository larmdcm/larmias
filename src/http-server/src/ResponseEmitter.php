<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\FileInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;
use Larmias\Contracts\Http\ResponseInterface;
use Larmias\HttpServer\Contracts\SseResponseInterface;
use Larmias\Support\Helper;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function method_exists;
use function rawurlencode;

class ResponseEmitter implements ResponseEmitterInterface
{
    /**
     * response emit.
     * @param PsrResponseInterface $psrResponse
     * @param ResponseInterface $response
     * @param bool $withContent
     * @return void
     */
    public function emit(PsrResponseInterface $psrResponse, ResponseInterface $response, bool $withContent = true): void
    {
        if ($psrResponse instanceof SseResponseInterface) {
            $sseEmitter = $psrResponse->getSseEmitter();
            if ($sseEmitter) {
                $sseEmitter->emit($response);
                return;
            }
        }

        $content = $psrResponse->getBody();

        $response = $response->withHeaders($psrResponse->getHeaders())
            ->status($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());

        if ($content instanceof FileInterface) {
            $response->sendFile($content->getFilename());
            return;
        }

        if (method_exists($psrResponse, 'getCookies')) {
            foreach ($psrResponse->getCookies() as $paths) {
                foreach ($paths ?? [] as $items) {
                    foreach ($items ?? [] as $cookie) {
                        if (Helper::isMethodsExists($cookie, [
                            'isRaw', 'getValue', 'getName', 'getExpiresTime', 'getPath', 'getDomain', 'isSecure', 'isHttpOnly', 'getSameSite',
                        ])) {
                            $value = $cookie->isRaw() ? $cookie->getValue() : rawurlencode($cookie->getValue());
                            $response->cookie(
                                $cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(),
                                $cookie->isSecure(), $cookie->isHttpOnly(), $cookie->getSameSite()
                            );
                        }
                    }
                }
            }
        }

        if ($withContent) {
            $response->end((string)$content);
        } else {
            $response->end();
        }
    }
}