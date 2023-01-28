<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Routing;

use Larmias\HttpServer\Contracts\RequestInterface;

class Url
{
    /**
     * @var string
     */
    protected string $url = '';

    /**
     * @var array
     */
    protected array $vars = [];

    /**
     * @var string
     */
    protected string $suffix = '';

    /**
     * @var string
     */
    protected string $domain = '';

    /**
     * @param RequestInterface $request
     */
    public function __construct(protected RequestInterface $request)
    {
    }

    /**
     * 设置URL
     *
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }


    /**
     * 设置URL参数
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
     * @param string $suffix
     * @return self
     */
    public function suffix(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * 设置URL域名
     * @param string $domain
     * @return self
     */
    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function build(): string
    {
        return '';
    }
}