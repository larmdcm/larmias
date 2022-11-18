<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Http\Message\ServerRequest;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\Routing\Dispatched;
use Larmias\Utils\Arr;

class Request extends ServerRequest implements RequestInterface
{
    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->getQueryParams();
        }
        return data_get($this->getQueryParams(), $key, $default);
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->getParsedBody();
        }
        return data_get($this->getParsedBody(), $key, $default);
    }

    /**
     * @param string|null $key
     * @param null $default
     * @return mixed
     */
    public function route(?string $key = null, $default = null): mixed
    {
        $route = $this->getAttribute(Dispatched::class);
        if (\is_null($route)) {
            return $default;
        }
        if (\is_null($key)) {
            return $route->params;
        }
        return \array_key_exists($key, $route->params) ? $route->params[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, mixed $default = null)
    {
        $data = $this->getInputData();

        return data_get($data, $key, $default);
    }

    /**
     * @param array $keys
     * @param null $default
     * @return array
     */
    public function inputs(array $keys, mixed $default = null): array
    {
        $data = $this->getInputData();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = data_get($data, $key, $default[$key] ?? null);
        }

        return $result;
    }

    /**
     * Retrieve a cookie from the request.
     * @param null|mixed $default
     */
    public function cookie(string $key, $default = null)
    {
        return data_get($this->getCookieParams(), $key, $default);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasCookie(string $key): bool
    {
        return !\is_null($this->cookie($key));
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function server(string $key, $default = null): mixed
    {
        return data_get($this->getServerParams(), $key, $default);
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    public function getPathInfo(): string
    {
        $requestUri = $this->getRequestTarget();

        // Remove the query string from REQUEST_URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }

        return $requestUri;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->getInputData();
    }

    /**
     * @return array
     */
    public function getInputData(): array
    {
        $body = $this->getParsedBody();
        $route = $this->route();
        return \array_merge(\is_array($route) ? $route : [], $this->getQueryParams(), \is_array($body) ? $body : []);
    }
}