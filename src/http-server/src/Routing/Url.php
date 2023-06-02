<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Routing;

use Larmias\HttpServer\Contracts\RequestInterface;
use Stringable;
use InvalidArgumentException;
use function parse_url;
use function in_array;
use function parse_str;
use function array_merge;
use function http_build_query;
use function preg_match_all;
use function str_replace;
use function str_contains;
use function ltrim;
use function pathinfo;
use function str_ends_with;
use const PHP_URL_PORT;
use const PATHINFO_EXTENSION;

class Url implements Stringable
{
    /**
     * @var string
     */
    protected string $suffix = '';

    /**
     * @var string
     */
    protected string $domain = '';

    /**
     * @var array
     */
    protected static array $config = [
        'domain' => '',
        'suffix' => '',
    ];

    /**
     * @param RequestInterface $request
     * @param string $url
     * @param array $vars
     */
    public function __construct(protected RequestInterface $request, protected string $url = '', protected array $vars = [])
    {
    }

    /**
     * 设置全局配置
     *
     * @param array $config
     */
    public static function setConfig(array $config): void
    {
        static::$config = array_merge(static::$config, $config);
    }

    /**
     * 设置URL
     *
     * @param string $url
     * @return self
     */
    public function url(string $url = ''): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 设置URL参数
     *
     * @param array $vars
     * @return self
     */
    public function vars(array $vars = []): self
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * 设置URL后缀
     *
     * @param string $suffix
     * @return self
     */
    public function suffix(string $suffix = ''): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * 设置URL域名
     *
     * @param string $domain
     * @return self
     */
    public function domain(string $domain = ''): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $url = $this->parseUrl($this->url);
        $domain = $this->domain;
        $parseUrl = parse_url($url);
        if (isset($parseUrl['host'])) {
            $domain = $parseUrl['host'];
        }
        if (isset($parseUrl['scheme'])) {
            $domain = $parseUrl['scheme'] . '://' . $domain;
        }
        if (isset($parseUrl['port']) && !in_array($parseUrl['port'], [80, 443])) {
            $domain = $domain . ':' . $parseUrl['port'];
        }
        $result = $this->parseDomain($domain);
        [$path, $vars] = $this->parsePath($parseUrl['path'] ?? '', $this->vars);
        if (!empty($path)) {
            if (!str_ends_with($path, '/')) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if (empty($extension)) {
                    $path .= $this->parseSuffix($this->suffix);
                }
            }
            $result .= '/' . ltrim($path, '/');
        }
        if (isset($parseUrl['query'])) {
            parse_str($parseUrl['query'], $params);
            $vars = array_merge($params, $vars);
        }
        if (!empty($vars)) {
            $result .= '?' . http_build_query($vars);
        }
        if (isset($parseUrl['fragment'])) {
            $result .= '#' . $parseUrl['fragment'];
        }
        return $result;
    }

    /**
     * 解析路径.
     *
     * @param string $path
     * @param array $vars
     * @return array
     */
    protected function parsePath(string $path, array $vars = []): array
    {
        if (preg_match_all('/\{(\w+)\:?.*?\}|\[\/\{(\w+).*?\}\]/', $path, $matches)) {
            foreach ($matches[0] as $key => $val) {
                $varName = $val[0] === '{' ? $matches[1][$key] : $matches[2][$key];
                if ($val[0] === '{' && !isset($vars[$varName])) {
                    throw new InvalidArgumentException('Error parsing path variable {' . $varName . '} is a required parameter');
                }
                $varVal = $vars[$varName] ?? '';
                if ($val[0] === '[' && !empty($varVal)) {
                    $varVal = '/' . $varVal;
                }
                unset($vars[$varName]);
                $path = str_replace($val, (string)$varVal, $path);
            }
        }
        return [$path, $vars];
    }

    /**
     * @param string $url
     * @return string
     */
    protected function parseUrl(string $url): string
    {
        $rule = Router::getRule($url);
        if ($rule) {
            $url = $rule->getRoute();
        }
        return $url;
    }

    /**
     * @param string $suffix
     * @return string
     */
    protected function parseSuffix(string $suffix): string
    {
        $suffix = $suffix ?: static::$config['suffix'];
        if (!empty($suffix)) {
            return str_contains($this->suffix, '.') ? $this->suffix : '.' . $this->suffix;
        }
        return '';
    }

    /**
     * @param string
     * @return string
     */
    protected function parseDomain(string $domain): string
    {
        $domain = $domain ?: static::$config['domain'];
        if (empty($domain)) {
            $domain = $this->request->getUri()->getHost();
        }
        $scheme = '';
        if (!str_contains($domain, '://')) {
            $scheme = $this->request->getUri()->getScheme() . '://';
        }

        $port = $this->request->getUri()->getPort();
        if ($port && !in_array($port, [80, 443])) {
            $domainPort = parse_url($domain, PHP_URL_PORT);
            if (!$domainPort) {
                $domain .= ':' . $port;
            }
        }

        return $scheme . $domain;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->build();
    }
}