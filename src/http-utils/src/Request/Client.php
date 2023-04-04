<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request;

use Larmias\Http\Message\Request;
use Larmias\Http\Message\Stream;
use Larmias\Http\Message\Uri;
use Larmias\Utils\Arr;
use Larmias\Utils\Codec\Json;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Larmias\Http\Utils\Request\Handler\RequestHandlerInterface;
use Larmias\Http\Utils\Request\Handler\CurlRequestHandler;
use Closure;
use function array_merge;
use function is_string;
use function ltrim;
use function str_contains;

class Client implements ClientInterface
{
    /**
     * @var array
     */
    protected array $options = [
        'base_uri' => null,
        'headers' => null,
        'body' => null,
        'body_type' => BodyType::RAW,
        'query' => null,
        'proxy' => null,
        'valid_https' => false,
        'timeout' => 10.0,
        'version' => '1.1',
        'cookie' => null,
        'handler' => CurlRequestHandler::class,
    ];

    /**
     * Client constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function get(string|UriInterface $uri, array $options = []): ResponseInterface
    {
        return $this->request('GET', $uri, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function post(string|UriInterface $uri, array $options = []): ResponseInterface
    {
        return $this->request('POST', $uri, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function put(string|UriInterface $uri, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $uri, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function patch(string|UriInterface $uri, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $uri, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function delete(string|UriInterface $uri, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function options(string|UriInterface $uri, array $options = []): ResponseInterface
    {
        return $this->request('OPTIONS', $uri, $options);
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param array $options
     * @return ResponseInterface
     */
    public function request(string $method, string|UriInterface $uri, array $options = []): ResponseInterface
    {
        $request = $this->applyOptions(new Request($method, $uri), $options);
        $handler = $this->makeHandler($options['handler']);
        return $handler($request, $options);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return ($this->makeHandler())($request);
    }

    /**
     * @param string|Closure|RequestHandlerInterface|null $handler
     * @return Closure
     */
    protected function makeHandler(string|Closure|RequestHandlerInterface $handler = null): Closure
    {
        return function (RequestInterface $request, array $options = []) use ($handler): ResponseInterface {
            if ($handler === null) {
                $handler = new CurlRequestHandler();
            } else if (is_string($handler)) {
                $handler = new $handler();
            } else {
                if ($handler instanceof Closure) {
                    return $handler($request, $options);
                }
            }
            /** @var RequestHandlerInterface $handler */
            return $handler->send($request, $options);
        };
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return RequestInterface
     */
    protected function applyOptions(RequestInterface $request, array &$options = []): RequestInterface
    {
        $options = array_merge($this->options, $options);
        if (!empty($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        if (!empty($options['base_uri'])) {
            $uri = $options['base_uri'] . '/' . ltrim((string)$request->getUri(), '/');
            $request = $request->withUri(new Uri($uri));
        }

        if (!empty($options['query'])) {
            $queryString = Arr::query($options['query']);
            $uri = (string)$request->getUri();
            $request = $request->withUri(new Uri($uri . (str_contains($uri, '?') ? '&' : '?') . $queryString));
        }

        if (!empty($options['version'])) {
            $request = $request->withProtocolVersion($options['version']);
        }

        if (!empty($options['body'])) {
            $body = match ($options['body_type'] ?? BodyType::RAW) {
                BodyType::FORM_SERIALIZE => Arr::query($options['body']),
                BodyType::JSON => Json::encode($options['body']),
                default => $options['body'],
            };
            $request = $request->withBody(Stream::create($body));

            if (!$request->hasHeader('Content-Type')) {
                $header = match ($options['body_type'] ?? BodyType::RAW) {
                    BodyType::FORM, BodyType::FORM_SERIALIZE => 'application/x-www-form-urlencoded',
                    BodyType::JSON => 'application/json',
                    default => '',
                };
                if (!empty($header)) {
                    $request = $request->withHeader('Content-Type', $header);
                }
            }

            if (!$request->hasHeader('User-Agent')) {
                $request = $request->withHeader('User-Agent', Utils::getDefaultUa());
            }
        }

        return $request;
    }
}