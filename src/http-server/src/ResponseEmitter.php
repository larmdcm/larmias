<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\FileInterface;
use Larmias\Contracts\Http\ResponseInterface as RawResponseInterface;
use Larmias\Utils\Helper;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Contracts\Http\ResponseEmitterInterface;
use function method_exists;
use function rawurlencode;

class ResponseEmitter implements ResponseEmitterInterface
{
    /**
     * response emit.
     * @param PsrResponseInterface $response
     * @param RawResponseInterface $rawResponse
     * @param bool $withContent
     * @return void
     */
    public function emit(PsrResponseInterface $response, RawResponseInterface $rawResponse, bool $withContent = true): void
    {
        $content = $response->getBody();

        $rawResponse = $rawResponse->withHeaders($response->getHeaders())
            ->status($response->getStatusCode(), $response->getReasonPhrase());

        if ($content instanceof FileInterface) {
            $rawResponse->sendFile($content->getFilename());
            return;
        }

        if (method_exists($response, 'getCookies')) {
            foreach ($response->getCookies() as $paths) {
                foreach ($paths ?? [] as $items) {
                    foreach ($items ?? [] as $cookie) {
                        if (Helper::isMethodsExists($cookie, [
                            'isRaw', 'getValue', 'getName', 'getExpiresTime', 'getPath', 'getDomain', 'isSecure', 'isHttpOnly', 'getSameSite',
                        ])) {
                            $value = $cookie->isRaw() ? $cookie->getValue() : rawurlencode($cookie->getValue());
                            $rawResponse->cookie(
                                $cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(),
                                $cookie->isSecure(), $cookie->isHttpOnly(), $cookie->getSameSite()
                            );
                        }
                    }
                }
            }
        }

        if ($withContent) {
            $rawResponse->end((string)$content);
        } else {
            $rawResponse->end();
        }
    }
}