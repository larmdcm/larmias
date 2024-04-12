<?php

declare(strict_types=1);

namespace Larmias\Guzzle\Handler;

use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use InvalidArgumentException;
use Larmias\Contracts\Client\Http\ClientFactoryInterface;
use Larmias\Contracts\Client\Http\ClientInterface;
use Larmias\Contracts\Client\Http\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class CoroutineHandler
{
    /**
     * @var int[]
     */
    protected static array $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * @param ClientFactoryInterface $clientFactory
     */
    public function __construct(protected ClientFactoryInterface $clientFactory)
    {
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();

        if (empty($path)) {
            $path = '/';
        }
        if ($query !== '') {
            $path .= '?' . $query;
        }

        $client = $this->makeClient($uri);

        // Init Headers
        $headers = $this->initHeaders($request, $options);
        // Init Settings
        $settings = $this->getSettings($request, $options);
        if (!empty($settings)) {
            $client->setOptions($settings);
        }

        $ms = microtime(true);

        try {
            $raw = $client->request($request->getMethod(), $path, (string)$request->getBody(), $headers, $request->getProtocolVersion());
        } catch (Exception $exception) {
            $exception = new ConnectException($exception->getMessage(), $request, null, [
                'errCode' => $exception->getCode(),
            ]);
            return Create::rejectionFor($exception);
        }

        $response = $this->getResponse($raw, $request, $options, microtime(true) - $ms);

        return new FulfilledPromise($response);
    }

    protected function makeClient(UriInterface $uri): ClientInterface
    {
        return $this->clientFactory->make((string)$uri);
    }

    protected function initHeaders(RequestInterface $request, array $options): array
    {
        $headers = $request->getHeaders();
        $userInfo = $request->getUri()->getUserInfo();
        if ($userInfo) {
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($userInfo));
        }

        return $this->rewriteHeaders($headers);
    }

    protected function rewriteHeaders(array $headers): array
    {
        // Unknown reason, Content-Length will cause 400 some time.
        // Expect header is not supported by \Swoole\Coroutine\Http\Client.
        unset($headers['Content-Length'], $headers['Expect']);
        return $headers;
    }

    protected function getSettings(RequestInterface $request, array $options): array
    {
        $settings = [];
        if (isset($options['delay']) && $options['delay'] > 0) {
            usleep(intval($options['delay'] * 1000));
        }

        // 验证服务端证书
        if (isset($options['verify'])) {
            $settings['ssl_verify_peer'] = false;
            if ($options['verify'] !== false) {
                $settings['ssl_verify_peer'] = true;
                $settings['ssl_allow_self_signed'] = true;
                $settings['ssl_host_name'] = $request->getUri()->getHost();
                if (is_string($options['verify'])) {
                    // Throw an error if the file/folder/link path is not valid or doesn't exist.
                    if (!file_exists($options['verify'])) {
                        throw new InvalidArgumentException("SSL CA bundle not found: {$options['verify']}");
                    }
                    // If it's a directory or a link to a directory use CURLOPT_CAPATH.
                    // If not, it's probably a file, or a link to a file, so use CURLOPT_CAINFO.
                    if (is_dir($options['verify'])
                        || (is_link($options['verify']) && is_dir(readlink($options['verify'])))) {
                        $settings['ssl_capath'] = $options['verify'];
                    } else {
                        $settings['ssl_cafile'] = $options['verify'];
                    }
                }
            }
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $settings['timeout'] = $options['timeout'];
        }

        // Proxy
        if (!empty($options['proxy'])) {
            $uri = null;
            if (is_array($options['proxy'])) {
                $scheme = $request->getUri()->getScheme();
                if (isset($options['proxy'][$scheme])) {
                    $host = $request->getUri()->getHost();
                    if (!isset($options['proxy']['no']) || !GuzzleHttp\Utils::isHostInNoProxy($host, $options['proxy']['no'])) {
                        $uri = new Uri($options['proxy'][$scheme]);
                    }
                }
            } else {
                $uri = new Uri($options['proxy']);
            }

            if ($uri) {
                $settings['http_proxy_host'] = $uri->getHost();
                $settings['http_proxy_port'] = $this->getPort($uri);
                if ($uri->getUserInfo()) {
                    [$user, $password] = explode(':', $uri->getUserInfo());
                    $settings['http_proxy_user'] = $user;
                    if (!empty($password)) {
                        $settings['http_proxy_password'] = $password;
                    }
                }
            }
        }

        // SSL KEY
        isset($options['ssl_key']) && $settings['ssl_key_file'] = $options['ssl_key'];
        isset($options['cert']) && $settings['ssl_cert_file'] = $options['cert'];

        // Swoole Setting
        if (isset($options['swoole']) && is_array($options['swoole'])) {
            $settings = array_replace($settings, $options['swoole']);
        }

        return $settings;
    }

    protected function getResponse(ResponseInterface $raw, RequestInterface $request, array $options, float $transferTime): Psr7\Response
    {
        $body = $raw->getBody();
        $sink = $options['sink'] ?? null;
        if (isset($sink) && (is_string($sink) || is_resource($sink))) {
            $body = $this->createSink($body, $sink);
        }

        $response = new Psr7\Response(
            $raw->getStatusCode(),
            $raw->getHeaders(),
            $body
        );

        if ($callback = $options[RequestOptions::ON_STATS] ?? null) {
            $stats = new TransferStats(
                $request,
                $response,
                $transferTime,
                $raw->getStatusCode(),
                []
            );

            $callback($stats);
        }

        return $response;
    }

    protected function createStream(string $body): StreamInterface
    {
        return Utils::streamFor($body);
    }

    /**
     * @param resource|string $stream
     */
    protected function createSink(string $body, $stream)
    {
        if (is_string($stream)) {
            $stream = fopen($stream, 'w+');
        }
        if ($body !== '') {
            fwrite($stream, $body);
        }

        return $stream;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getPort(UriInterface $uri): int
    {
        if ($port = $uri->getPort()) {
            return $port;
        }
        if (isset(self::$defaultPorts[$uri->getScheme()])) {
            return self::$defaultPorts[$uri->getScheme()];
        }
        throw new InvalidArgumentException("Unsupported scheme from the URI {$uri->__toString()}");
    }
}