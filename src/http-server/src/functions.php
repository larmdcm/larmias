<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Context\ApplicationContext;
use Larmias\HttpServer\Routing\Url;

/**
 * 获取URL对象
 * @param string $url
 * @param array $vars
 * @return Url
 */
function url(string $url = '', array $vars = []): Url
{
    /** @var Url $url */
    $url = ApplicationContext::getContainer()->make(Url::class, ['url' => $url, 'vars' => $vars], true);
    return $url;
}

/**
 * 构建URL字符串
 * @param string $url
 * @param array $vars
 * @return string
 */
function url_string(string $url = '', array $vars = []): string
{
    return url($url, $vars)->build();
}
