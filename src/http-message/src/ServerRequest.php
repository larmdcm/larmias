<?php

declare(strict_types=1);

namespace Larmias\Http\Message;

use Larmias\Collection\Arr;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;
use Larmias\Contracts\Http\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\UriInterface;
use function str_contains;
use function explode;

class ServerRequest extends Request implements ServerRequestInterface
{
    /** @var array */
    protected array $serverParams = [];

    /** @var array */
    protected array $cookieParams = [];

    /** @var array */
    protected array $queryParams = [];

    /** @var array */
    protected array $uploadedFiles = [];

    /** @var array */
    protected array $attributes = [];

    /** @var array|object|null */
    protected mixed $parsedBody = null;

    /**
     * @param array $params
     * @return ServerRequestInterface
     */
    public static function create(array $params): ServerRequestInterface
    {
        $serverRequest = new static(
            $params['method'],
            $params['uri'],
            $params['header'],
            $params['rawBody'],
            $params['protocolVersion']
        );
        $serverRequest->serverParams = $params['server'] ?? [];
        $serverRequest->queryParams = $params['query'] ?? [];
        $serverRequest->parsedBody = $params['post'] ?? [];
        $serverRequest->cookieParams = $params['cookie'] ?? [];
        $files = $params['file'] ?? [];
        foreach ($files as $name => $file) {
            if (Arr::isList($file)) {
                foreach ($file as $item) {
                    $serverRequest->uploadedFiles[$name][] = new UploadedFile($item['tmp_name'], $item['size'], $item['error'], $item['name'], $item['type']);
                }
            } else {
                $serverRequest->uploadedFiles[$name] = new UploadedFile($file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type']);
            }
        }
        return $serverRequest;
    }

    /**
     * @param HttpRequestInterface $request
     * @return ServerRequestInterface
     */
    public static function loadFromRequest(HttpRequestInterface $request): ServerRequestInterface
    {
        return static::create([
            'method' => $request->method(),
            'uri' => static::getUriFromRequest($request),
            'header' => $request->header(),
            'rawBody' => $request->rawBody(),
            'protocolVersion' => $request->protocolVersion(),
            'server' => $request->server(),
            'query' => $request->query(),
            'post' => $request->post(),
            'cookie' => $request->cookie(),
            'file' => $request->file(),
        ]);
    }

    /**
     * @param HttpRequestInterface $request
     * @return UriInterface
     */
    protected static function getUriFromRequest(HttpRequestInterface $request): UriInterface
    {
        $uri = new Uri();
        $header = $request->header();
        $uri = $uri->withScheme($request->schema())->withPath($request->path())->withQuery($request->queryString());
        if (isset($header['host'])) {
            if (str_contains($header['host'], ':')) {
                [$host, $port] = explode(':', $header['host'], 2);
                if ($port !== $uri->getDefaultPort()) {
                    $uri = $uri->withPort($port);
                }
            } else {
                $host = $header['host'];
            }
            $uri = $uri->withHost($host);
        }

        return $uri;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies): self
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query): self
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    /**
     * @return mixed
     */
    public function getParsedBody(): mixed
    {
        return $this->parsedBody;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function withParsedBody(mixed $data): self
    {
        /** @var mixed $data */
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException('Parsed body value must be an array, an object, or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     * @see getAttributes()
     */
    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     * @see getAttributes()
     */
    public function withAttribute($name, $value): self
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param string $name The attribute name.
     * @return static
     * @see getAttributes()
     */
    public function withoutAttribute($name): self
    {
        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }
}